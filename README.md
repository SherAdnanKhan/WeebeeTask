Installation step


    Composer install

    php artisan key:generate
    
    php artisan migrate
    
    php artisan db:seed
    
    php artisan serv
    

Postman 

    http://127.0.0.1:8000//api/appointments

    // payload
    {
        "service_id": 1,
        "appointment_start_time": "2023-07-01 12:10:00",
        "users": [
            {
                "first_name": "John",
                "last_name": "Doe",
                "email": "john@example.com"
            }

        ]
    }

    http://testproject.test/api/index/1/2023-06-30
