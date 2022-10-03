# ApiAuthenticationJwt

Clone project:
```
git clone git@github.com:fpiccolo/ApiAuthenticationJwt.git
```

Enter the project directory:
```
cd ApiAuthenticationJwt
```

Initialize the project:
```
make init
```

This command will do:
- the build of the docker images
- will docker-compose up
- will initialize the database
- will perform the migrations
- will create the two default user

The default users are:

User 1:
- email: `fra@gmail.com`
- password: `password`
- rolse: [ROLE_ADMIN, ROLE_USER]

User 2:
- email: `luca@gmail.com`
- password: `password`
- rolse: [ROLE_USER]
