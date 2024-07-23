```
git add . && git commit -m "++++" && git push -u origin main
```

```
composer require barryvdh/laravel-debugbar --dev

```


```
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

```


---

git checkout --orphan latest_branch
git add -A && git commit -am "Initial Commit"
git branch -D main
git branch -m main
git push -f origin main
