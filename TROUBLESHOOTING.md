# Troubleshooting

## Segmentation fault during build

Should you encounter a segfault during builds: 

```
bin/compile: line 603:  3547 Segmentation fault      composer config --no-plugins
```

Try purging your build cache:

```bash
heroku plugins:install heroku-repo
heroku repo:purge_cache -a my-app
```
