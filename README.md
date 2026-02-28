# HormoneLens Repository

This is the monorepo for HormoneLens backend and other components.

## Artisan helper

The Laravel application lives in the `backend/` directory, so the usual
artisan binary is located there. To make life easier you can run commands
from the root of the repository using one of the following:

```sh
# proxy script shipped at repository root
php artisan serve
# or equivalent
./artisan serve

# explicitly specify path
php backend/artisan serve

# or change into the backend directory
cd backend && php artisan serve
```

The root-level `artisan` script simply forwards any arguments to
`backend/artisan`, which avoids "Could not open input file: artisan" errors.

Make sure the script is executable (`chmod +x artisan`).

## Other information

... (add more project documentation as needed) ...
