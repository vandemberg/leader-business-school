test:
	php artisan test

clean:
	php artisan cache:clear
	php artisan config:clear
	php artisan config:cache
