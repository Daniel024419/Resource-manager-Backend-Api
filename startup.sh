#!/bin/bash
set -e

php artisan migrate:fresh --seed

# Start the Laravel development server
exec php artisan serve --host 0.0.0.0 --port 8003

# Run any setup commands here
exec php artisan schedule:work