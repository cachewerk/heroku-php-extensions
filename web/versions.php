<?php
// versions.php
// Generates HTML tables of extension versions from hardcoded packages.json URLs.

// Hardcoded packages.json URLs (edit as needed)
$urls = [
    'https://heroku-php-extensions.s3.amazonaws.com/dist-heroku-24-amd64-stable/packages.json',
    'https://heroku-php-extensions.s3.amazonaws.com/dist-heroku-22-stable/packages.json',
    'https://heroku-php-extensions.s3.amazonaws.com/dist-heroku-20-stable/packages.json',
    'https://heroku-php-extensions.s3.amazonaws.com/dist-heroku-18-stable/packages.json'    
];

$relay = new Relay\Relay;
$relay->connect(getenv('UPSTASH_REDIS_REST_URL'), 6379);
$relay->auth(['default', getenv('UPSTASH_REDIS_REST_TOKEN')]);

$table = new Relay\Table('packages');

function fetch_json(string $url, $table): ?array 
{
    if($table->exists($url)) {
        $cached = $table->get($url);
        if ($cached) {
            return json_decode($cached, true);
        }
    }
    $opts = [
        'http' => [
            'method' => 'GET',
            'header' => "User-Agent: PHP\r\n",
            'timeout' => 10,
        ],
    ];
    $context = stream_context_create($opts);
    $json = @file_get_contents($url, false, $context);
    if ($json === false) {
        error_log("Failed to fetch: $url");
        return null;
    }
    $table->set($url, $json);
    $data = json_decode($json, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("JSON decode error for $url: " . json_last_error_msg());
        return null;
    }
    return $data;
}

function parse_packages(array $packages_data): array
{
    $stacks = [];
    if (!isset($packages_data['packages'])) {
        return $stacks;
    }

    $packages = $packages_data['packages'];
    $package_list = [];

    // If associative array (package-name => [versions])
    $is_assoc = array_keys($packages) !== range(0, count($packages) - 1);
    if ($is_assoc) {
        foreach ($packages as $package_name => $versions) {
            if (is_array($versions)) {
                foreach ($versions as $version_info) {
                    $package_list[] = $version_info;
                }
            }
        }
    } else {
        // Flatten lists
        foreach ($packages as $item) {
            if (is_array($item) && array_keys($item) === range(0, count($item) - 1)) {
                foreach ($item as $sub) {
                    $package_list[] = $sub;
                }
            } else {
                $package_list[] = $item;
            }
        }
    }

    foreach ($package_list as $version_info) {
        if (!is_array($version_info)) {
            continue;
        }
        $package_name = $version_info['name'] ?? null;
        if (!$package_name || strpos($package_name, 'heroku-sys/ext-') !== 0) {
            continue;
        }
        $ext_name = substr($package_name, strlen('heroku-sys/ext-'));
        $ext_version = $version_info['version'] ?? null;
        if (!$ext_version) {
            continue;
        }
        $require = $version_info['require'] ?? [];
        $php_req = $require['heroku-sys/php'] ?? null;
        $stack_req = $require['heroku-sys/heroku'] ?? null;
        if (!$php_req || !$stack_req) {
            continue;
        }

        // Parse stack (e.g., ^22.0.0 -> 22 -> heroku-22)
        if (preg_match('/\^?(\d+)\./', $stack_req, $m)) {
            $stack = 'heroku-' . $m[1];
        } else {
            continue;
        }

        // Parse PHP version like 7.3.* -> 7.3
        if (preg_match('/(\d+\.\d+)\.\*/', $php_req, $m2)) {
            $php_version = $m2[1];
        } else {
            continue;
        }

        $stacks[$stack][$php_version][$ext_name][] = $ext_version;
    }

    // Deduplicate and sort versions
    foreach ($stacks as $stack_name => $phps) {
        foreach ($phps as $php => $exts) {
            foreach ($exts as $ext => $versions) {
                $unique = array_values(array_unique($versions));
                usort($unique, function ($a, $b) {
                    return version_compare($a, $b);
                });
                $stacks[$stack_name][$php][$ext] = $unique;
            }
        }
    }

    return $stacks;
}

// Merge stacks from multiple manifests
$all_stacks = [];
foreach ($urls as $url) {
    $data = fetch_json($url, $table);
    if ($data === null) {
        continue;
    }
    $st = parse_packages($data);
    // Merge into all_stacks
    foreach ($st as $stack => $phps) {
        foreach ($phps as $php => $exts) {
            foreach ($exts as $ext => $versions) {
                if (!isset($all_stacks[$stack][$php][$ext])) {
                    $all_stacks[$stack][$php][$ext] = [];
                }
                $all_stacks[$stack][$php][$ext] = array_merge($all_stacks[$stack][$php][$ext], $versions);
            }
        }
    }
}

// Final dedupe and sort after merge
foreach ($all_stacks as $stack_name => $phps) {
    foreach ($phps as $php => $exts) {
        foreach ($exts as $ext => $versions) {
            $unique = array_values(array_unique($versions));
            usort($unique, function ($a, $b) {
                return version_compare($a, $b);
            });
            $all_stacks[$stack_name][$php][$ext] = $unique;
        }
    }
}

// Helper to sort php versions like 7.3, 8.0 numerically
function sort_php_versions(array $versions): array
{
    usort($versions, function ($a, $b) {
        $pa = array_map('intval', explode('.', $a));
        $pb = array_map('intval', explode('.', $b));
        for ($i = 0; $i < max(count($pa), count($pb)); $i++) {
            $va = $pa[$i] ?? 0;
            $vb = $pb[$i] ?? 0;
            if ($va === $vb) continue;
            return $va <=> $vb;
        }
        return 0;
    });
    return $versions;
}

// Render HTML using site header/footer and Tailwind styling
ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/header.phtml';

if (empty($all_stacks)) {
    echo "<section class=\"max-w-2xl mx-auto\"><p class=\"text-sm text-gray-500\">No data available.</p></section>\n";
    require __DIR__ . '/footer.phtml';
    exit;
}

foreach ($all_stacks as $stack => $phps) {
    // gather all extensions and php versions
    $all_extensions = [];
    $all_php_versions = [];
    foreach ($phps as $php => $exts) {
        $all_php_versions[] = $php;
        foreach ($exts as $ext => $_) {
            $all_extensions[$ext] = true;
        }
    }
    $all_extensions = array_keys($all_extensions);
    sort($all_extensions, SORT_NATURAL | SORT_FLAG_CASE);
    $all_php_versions = array_values(array_unique($all_php_versions));
    $all_php_versions = sort_php_versions($all_php_versions);

    echo "<section class=\"max-w-4xl mx-auto mt-6\">\n";
    echo "  <h3 class=\"text-lg leading-6 font-medium text-gray-900\">" . htmlspecialchars($stack) . "</h3>\n";
    echo "  <div class=\"mt-4 overflow-x-auto\">\n";
    echo "    <table class=\"min-w-full divide-y divide-gray-200\">\n";
    echo "      <thead class=\"bg-gray-50\"><tr><th class=\"px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider\">PHP</th>";
    foreach ($all_extensions as $ext) {
        echo "<th class=\"px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider\">" . htmlspecialchars($ext) . "</th>";
    }
    echo "</tr></thead>\n";
    echo "      <tbody class=\"bg-white divide-y divide-gray-200\">\n";

    foreach ($all_php_versions as $php_ver) {
        echo "        <tr>\n";
        echo "          <td class=\"px-3 py-2 text-sm text-gray-700\">" . htmlspecialchars($php_ver) . "</td>";
        foreach ($all_extensions as $ext) {
            $versions = $all_stacks[$stack][$php_ver][$ext] ?? [];
            $cells = [];
            foreach ($versions as $v) {
                $cells[] = '<code class="text-xs bg-gray-100 px-2 py-1 rounded inline-block m-px">' . htmlspecialchars($v) . '</code>';
            }
            echo "<td class=\"px-3 py-2 text-sm text-gray-700\">" . implode(' ', $cells) . "</td>";
        }
        echo "</tr>\n";
    }

    echo "      </tbody>\n";
    echo "    </table>\n";
    echo "  </div>\n";
    echo "</section>\n";
}

require __DIR__ . '/footer.phtml';
