<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400"></a></p>

# Index

-   [Getting Started](#getting-started)
-   [The interface of a Credential](#the-interface-of-a-credential)
-   [Authentication System](#authentication-system)
-   [Running the tests](#running-the-tests)
-   [The Documentation](#the-documentation)
-   [Dependencies](#packages-used-for-this-project)

<br/>

# Getting Started

Before you can start with this project, you need to first prepare all the environment variables, you will have to create the .env file on the root directory. You can use the .env.example file found on the root directory as a reference.

In order to install all the required dependencies, execute:

    composer install

Once thats done, you'll have to generate a new key. You can do it by executing:

    php artisan key:generate

About the database, I used a MySQL database. You can run the migrations and seeders by executing:

    php artisan migrate --seed

At last, I decided to use JWT as the authentication system for the API. Before using the API you'll have to generate a JWT secret. You can do it by executing the following:

    php artisan jwt:secret

Once all of that is done, you can fully start to work with this project.

<br/>

# The Interface of a Credential

Instead of thinking of "passwords" and "usernames" I decided to try a different thing, more like "accounts" one Credential may have multiple attributes, depending on the users' need.

For example, someone that may have registered to a site using a 2-factor-authentication app, like google Authenticator. Usually when you register using this method, the app or service gives you a recovery key. Instead of writing it on a piece of paper, you can store in PasuNashi as a unique code inside of the Credential.

A credential may have 2 states, an encrypted state, and an decrypted state.

<br />

## Properties of an Ecrypted Credential:

```
{
    id: integer,
    user_id: integer,
    company_id: null | integer,
    company: null | {
        id: integer,
        name: string,
        url_logo: string,
        created_at: string,
        updated-at: string,
    },
    company_name: null | string,
    last_seen: string,
    accessing_device: string,
    accessing_platform: "web" | "desktop" | "mobile",
    char_count: integer,
    description: string,
    email: null | {
        id: integer,
        opening: string,
        char_count: integer,
        ending: string,
    },
    password: null | {
        id: integer,
        char_count: integer,
    },
    phone_number: null | {
        id: integer,
        opening: string,
        char_count: integer,
        ending: string,
    },
    security_codes: null | {
        id: integer,
        unique_code_length: null | integer,
        multiple_codes_length: null | integer,
        crypto_codes_length: null | integer
    },
    security_question_answer: null | {
        id: integer,
    }
    created_at: string,
    updated_at: string,
}
```

### The properties:

company_id, company, company_name are realetd to the associated company. Example:

```
{
    company_id: 1,
    company_name: "Google",
    company: {
        id: 1,
        name: "Google",
        url_logo: "https://google.com/logo.png"
    }
}
```

last_seen Is the last date when that credential was accessed. By default its the creation & update dates. Example: "2021-10-23 15:53:32".

accessing_device Is the user agent, or the unique id of the device.
accessing_platform must be given by the frontend, and it must be one of those three options.

char_count This property is the length of the name that the user registered with for that credential (in the decrypted version will be "user_name").

You may have noticed that some inner properties, like "email" have an opening, ending and char_count. It was made like that, in order to show the user the beggining and the end of some things, without decrypting them. for example:

"fake@email.com"

```
{
    id: 21,
    opening: "fa",
    char_count: 2,
    ending: "@email.com"
}
```

(The char_count counts all the characters between the opening and the ending). So this email can be shown in a way like this: "fa\*\*@email.com".

<br/>

### The security codes

The decrypted unique_code will be a string, so the unique_code_lenght is the total characters of that string.

The other two codes are an array of strings. So their length represents the amount of strings in both arrays

<br/>

And for the last of the Credential's properties, the security question and the security answer will be two separated strings. These two don't include a char_count for each one, beacuse just the length could be a hint of what they are.

<br/>

## The properties of a Decrypted Credential

```
{
    id: integer,
    user_id: integer,
    company_id: null | integer,
    company_name: null | string,
    description: string,
    user_name: null | string,
    email: undefined | string,
    password: undefined | string,
    username: undefined | string,
    phone_number: undefined | string,
    security_question: undefined | string,
    security_answer: undefined | string,
    unique_code: undefined | string,
    multiple_codes: undefined | string[],
    crypto_codes: undefined | string[],
    last_seen: string,
    created_at: string,
    updated_at: string
}
```

<br />

# The Authentication System

In order for this API to register users' sessions, I decided to use JWT.

To access routes that require authentication, you must send the JWT Token in the header of the request like this:

    "Authorization": "Bearer paste-here-your-jwt-token"

(remember to keep the space after "Bearer").

<br/>

# Running the Tests

This app counts with automated Feature Tests. For executing them, you'll first have to set up the phpunit.xml file found in the root directory. Add a connection for the database, the queue system, and etc.

## Important

If you're using MacOS Big Sur or superior, you may need to install PHP 8, because the default php that MacOS has is broken and doesn't recon¡gnize the tests.

To execute the tests you can run either one of these commands:

    php artisan test

To run them one after another, or:

    php artisan test --parallel

To run them in parallel.

You can find all the tests in ./tests/Feature

(If you have any problem running the tests, you may have to create a folder inside tests, that has "Unit" as the name).

<br/>

# The Documentation

I generated all the API endpoints documentation using the package Scribe for Laravel. You can find it [Here](https://pasunashi-backend.herokuapp.com/docs).

In adition to that, if you want to know a bit more of the routes, you can see all the information for all available routes [Here](https://pasunashi-backend.herokuapp.com/routes).

<br/>

# Dependencies

This project makes use of the following packages:

-   **garygreen/pretty-routes**: **^1.0** Used to show all the information about all available routes.

-   **league/flysystem-aws-s3-v3**: **^1.0** To manage the storage for the logos of the companies available in the database.

-   **pragmarx/google2fa**: **^8.0** For enabling the 2FA TOTP login (The login using Google Authenticator, or any other similar app that generates unique numbers each 30 seconds). I personally don't recommend using it, but I couldn't find any other similar to use here.

-   **spatie/laravel-permission**: **^4.0** To manage roles and permissions.

-   **tymon/jwt-auth**: **^1.0** To register users' session on the API.
