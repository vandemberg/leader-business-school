up:
	docker compose up -d

test:
	php artisan test

vite:
	docker compose exec vite ssh

clean:
	php artisan cache:clear
	php artisan config:clear
	php artisan config:cache

ssh:
	docker compose exec app bash

c:
	docker compose exec app php artisan tinker
