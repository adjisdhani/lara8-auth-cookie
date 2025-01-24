# Laravel 8 Cookie-Based Authentication with Sanctum

This guide demonstrates how to set up Laravel 8 with cookie-based authentication using Sanctum. Follow these steps to configure and test the API.

---
### The installation is divided into A and B, A is for installing Laravel from scratch, B is from this clone project

---

## A. Installation Steps

### 1. Create a New Laravel Project
```bash
composer create-project --prefer-dist laravel/laravel laravel-cookie-auth "8.*"
cd laravel-cookie-auth
```

### 2. Install Sanctum
```bash
composer require laravel/sanctum
```

### 3. Publish Sanctum Configuration
```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

### 4. Run Migrations
```bash
php artisan migrate
```

### 5. Update Middleware in `app/Http/Kernel.php`
Ensure the `api` middleware group contains the following:
```php
'api' => [
    \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    'throttle:api',
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
],
```

### 6. Update `User` Model
Add the `HasApiTokens` trait to the `User` model in `app/Models/User.php`:
```php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
}
```

### 7. Define Routes in `routes/api.php`
Set up the following routes:
```php
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

Route::get('/login', function () {
    return response()->json(['message' => 'Please login'], 401);
})->name('login');

Route::middleware('web')->group(function () {
    Route::post('/login', function (Request $request) {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return response()->json(['message' => 'Login successful'], 200);
        }

        return response()->json(['message' => 'Invalid credentials'], 401);
    });

    Route::post('/logout', function (Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logged out'], 200);
    });

    Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
        return response()->json($request->user());
    });
});
```

### 8. Update Sanctum Configuration
In `config/sanctum.php`, set the `stateful` domains:
```php
'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', 'localhost,127.0.0.1')),
```

### 9. Add Environment Variables in `.env`
Add the following settings to `.env`:
```env
SESSION_DOMAIN=localhost
SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1
```

### 10. Create a User Seeder
Run the following command:
```bash
php artisan make:seeder UserSeeder
```

Update the seeder file `database/seeders/UserSeeder.php` with the following:
```php
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);
    }
}
```

Run the seeder to create a test user:
```bash
php artisan db:seed --class=UserSeeder
```

### 11. Update CSRF Middleware
Exclude the login and logout routes from CSRF protection in `app/Http/Middleware/VerifyCsrfToken.php`:
```php
protected $except = [
    'api/login',
    'api/logout',
];
```

### 12. Start the Laravel Server
Run the server using:
```bash
php artisan serve
```

---

## Testing with Postman
Follow these steps to test the API:

1. **Get CSRF Token**
   - Hit the endpoint `GET /sanctum/csrf-cookie`.
   - Ensure the `XSRF-TOKEN` and `laravel_session` cookies are stored.

2. **Login**
   - Send a `POST` request to `/api/login` with the following body:
     ```json
     {
         "email": "test@example.com",
         "password": "password"
     }
     ```
   - You should receive a response: `{ "message": "Login successful" }`.

3. **Get User**
   - Send a `GET` request to `/api/user`.
   - Ensure the `X-XSRF-TOKEN` header is set with the token value from the `XSRF-TOKEN` cookie.
   - You should receive the user details.

4. **Logout**
   - Send a `POST` request to `/api/logout`.
   - You should receive a response: `{ "message": "Logged out" }`.

---

## B. Installation Steps

1. **Clone the repository**:
   ```bash
   git clone https://github.com/adjisdhani/lara8-auth-cookie.git
   ```

2. **Navigate to the project directory**:
   ```bash
   cd lara8-auth-cookie
   ```

3. **Install dependencies**:
   ```bash
   composer install
   ```

4. **Generate the application key**:
   ```bash
    php artisan key:generate
    ```
5. **Configure the .env file**:
   ```bash
    DB_CONNECTION=mysql
	DB_HOST=127.0.0.1
	DB_PORT=3306
	DB_DATABASE=lara8-auth-cookie
	DB_USERNAME=root
	DB_PASSWORD=yourpassword

6. The next step follows the steps in point A (3-12)

That's it! Your Laravel API with cookie-based authentication is now ready.
