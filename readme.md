# How to build project
1) docker-compose up -d --build
2) docker exec -it mintos-test-app-1 /bin/bash
   
    where _mintos-test-app-1_ container name
   
4) php bin/console doctrine:migrations:migrate

# Endpoints:

### /v1/clients/{clientId}/accounts

#### method: GET

response example:
```json
[
    {
        "id": 1,
        "balance": {
        "amount": 500,
        "currency": "EUR"
    },
        "createdAt": null
    }
]
```

### /v1/accounts/{accountId}/transactions?limit=3&offset=1

### #method: GET
##### optional query params:
_int_ limit

_int_ offset

response example:
```json
    [
        {
            "id": 8,
            "account": {
                "id": 1,
                "balance": {
                    "amount": 500,
                    "currency": "EUR"
                },
                "createdAt": null
            },
            "amount": {
                "amount": 10.53,
                "currency": "USD"
            },
            "createdAt": null
        }
    ]
```

### /v1/accounts/transfer
#### method: POST
##### required post params:
_int_ recipientAccountId

_int_ senderAccountId

_float_ amount

_string_ currency

response example:
```json
{"message":"Money transferred successfully"}
```
