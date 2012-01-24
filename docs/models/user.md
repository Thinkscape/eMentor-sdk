eMentor API v2 documentation
--------------------------------

# "user" model #

Introduction
==============

A `user` item is a single client account. It is identified by unique email address. Each client can have multiple
concurrent [users](user.md) and can have access to one or more [products](product.md).

### Registering users

You can only access user accounts that have been registered with your brand. In order to register a user you have to
send a `POST` REST request to API Server with, at minimum, `email` and `registerIP` fields. From this moment the user
will be accessible with `id` equal to his/her `email` address.


Attributes
==============

  **Name**         | **Format**    | **Required** | **Access** | **Description**
------------------ | ------------- | ------------ | ---------- | --------------------
  `id`             | string        |              | read-only  | User email, must be present when creating new user instance
  `type`           | integer       |              | read-only  | One of: 0 (user), 8 (affiliate), 64 (author),
  `typeName`       | string        |              | read-only  | One of: user, author, affiliate
  `status`         | integer       |              | read-only  | Current user status. For a list of available status values, see next section.
  `statusName`     | string        |              | read-only  | user status name.
  `email`          | string        | **Required** | write+read | User email, same as id.
  `dateCreated`    | integer       |              | read-only  | Date the user has been created in the database (unix timestamp, seconds since unix epoch)
  `dateCreatedIso` | string        |              | read-only  | Date the user has been created in the database (ISO 8601)
  `dateLogged`     | integer       |              | read-only  | Last login date (unix timestamp, seconds since unix epoch)
  `dateLoggedIso`  | string        |              | read-only  | Last login date (ISO 8601)
  `registerIP`     | string        | **Required** | write once | User browser's IP address at the moment of account creation.


User status
==============

 **Status value**   |  **Name**         | **Can be set?** | **Description**
 ------------------ | ----------------- | ---------------------------------------------------------
 200                | ACTIVEAFF         | NO              | Active affiliate user.
 100                | ACTIVE            | NO              | Active user.
 50                 | SUSPENDED         | NO              | Suspended affiliate user.
 10                 | UNCONFIRMED       | NO              | Awaiting email confirmation.
 0                  | USER              | NO              | Temporary user.
 -60                | SUSPENDED         | NO              | Account suspended by admin.
 -80                | DELETED_BY_OWNER  | NO              | Account deleted on user's request.
 -100               | DELETED           | NO              | Account deleted by admin.

Associations
==============

### /order

 * **model**: [order](order.md)
 * **count**: 0 or more
 * **access**: write+read

This association holds user's orders.

For example, to retrieve all orders of user `martin@me.com` we could use the following REST request:

    GET /api/v2/rest/user/martin@me.com/order

To create new order for this user:

    POST /api/v2/rest/user/martin@me.com/item
    Content-Type: text/json

    {referrerUrl:"http://campaign.com/landing-page"}

### /product

 * **model**: [product](product.md)
 * **count**: 0 or more
 * **access**: read-only

This association holds all products that this user has access to.

For example, to retrieve all products of user `martin@me.com` with price greater than 100 PLN:

    GET /api/v2/rest/user/martin@me.com/product?filter-price-gt=100




Examples
==============



----
![](http://www.ementor.pl/img/logo-white.png)
