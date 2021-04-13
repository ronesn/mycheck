 simple authentication system.
 implemented in laravel
 The app contain 3 Restful API endpoints:
1. User registration
    a. POST  name, email & password
2. User Login
    a. The user sends GET REQ' email & password and receives token
3. User data
    a. The user sends GET REQ' with token and receives his name back
use mysql laravel db name (user:root,password:none);
