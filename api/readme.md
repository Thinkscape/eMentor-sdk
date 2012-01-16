eMentor API v2 documentation
--------------------------------

 TOC
======
 
 1. [Technical overview]                    (#overview)
 2. [Authenticating requests]               (#keys)
 3. [RESTful API]                           (#rest)
     1. [List URL]                          (#rest1)
     2. [General list params]               (#rest2)
     3. [Filtering params]                  (#rest3)
     4. [Item URL]                          (#rest4)
     5. [Item query methods]                (#rest5)
     6. [Changing query data format]        (#rest6)
     7. [Changing query result format]      (#rest7)
 4. [Direct API]                            (#direct)
     1. [Request format]                    (#direct1)
     2. [Result formats]                    (#direct2)
     3. [Available methods]                 (#direct3)


Technical overview                                                               <a name="overview"></a>
===================
eMentor system can be accessed via [RESTful API](#rest) and [Direct API](#direct). All connections to the API server
are made via HTTPS (port 443) on a dedicated API URL. RESTful and Direct methods are complementary. As a general rule,
you should use REST for obtaining and manipulating object data and Direct API for all other operations not covered by
REST.

All requests to the API are signed with unique API Key, as [described in the next chapter](#keys). You can obtain it
by logging to [Author's Panel][APanel] with your email and password.

API server can return response in several formats, including

 * JSON
 * XML (schema-less)
 * PHP array (serialize)


Signing requests                                                                 <a name="keys"></a>
===================
To use eMentor API you must have an **API KEY**. Each key consist of:

 * API Key ID
 * API Key Secret

You can generate new key by logging into [Author's Panel][APanel]

> Warning! eMentor API does not provide per-key fine-grain security.
> Each key allows full write access to your whole account with us and, if obtained by a third party, can result
> in serious data loss and/or privacy issues. 

> Always protect your Key ID and Secret!!!



All requests **must** be signed using your API Key. In order to sign a request you have to append `Authorize` header.

Below is an example request sent to eMentor API server:

    POST /api/rest/training/9kw2lkaw HTTP/1.0
    Content-Md5: c8fdb181845a4ca6b8fec737b3581d76
    Content-Type: text/html
    Date: Sun, 1 Jan 2012 11:30:01 GMT
    Authorization: EMT 62CS22QCUpQ57CTG MWEwODJiN2QyNzQ4MjlmMWY3MWRkZmY4MjJhNGJhYWY4YWY5OWIxYQ==



### Authorization Header format

All requests have to have the following HTTP header:

    Authorization: EMT [API KEY ID] [SIGNATURE]

**API KEY ID** - id for key generated and obtained from Author's Panel.

**SIGNATURE** - BASE64-encoded [SHA1][] checksum for the request. The checksum is calculated from a concatenated
string consisting of:

 1. HTTP verb (i.e. "POST")
 2. Resource URL (including query parameters)
 3. Content-Type (i.e. "text/json", or empty)
 4. Content-Md5 (if any)
 5. Date (cannot deviate from API server time by more than 15 minutes)
 6. Key Secret

To generate signature in PHP 5.3 you can use the following code:

    $checksumString = $httpVerb . $url . $contentType. $contentMd5 . $date . $keySecret;
    $signature = base64_encode( sha1( $checksumString ) );




### Example 1
Assuming our Key ID is `62CS22QCUpQ57CTG` and Key Secret is `7mq6gEEL3V1ijnUl7MbWSGix6s0I3NN7` we are signing
the following request

    GET /api/rest/training/9kw2lkaw HTTP/1.0
    Date: Sun, 1 Jan 2012 11:30:01 GMT

In this example, there are no `Content-Type` or `Content-Md5` headers, so we will calculate a signature using only
HTTP verb, date and key secret. For example, in PHP 5.3 we would use the following code:

    <?php
    $verb = 'GET';    
    $url = '/api/rest/training/9kw2lkaw';
    $date = 'Sun, 1 Jan 2012 11:30:01 GMT';
    $keySecret = '7mq6gEEL3V1ijnUl7MbWSGix6s0I3NN7';
    $keyID = '62CS22QCUpQ57CTG';
        
    $authorization = 'EMT '. $keyID . ' ' .base64_encode(sha1( $verb . $url . $date . $keySecret ));
    echo $authorization;

The result:

    EMT 62CS22QCUpQ57CTG NTIwNjQzMTE2MDFlZGI4M2M4OTU5YjAyNTE2ZmIwMDg5NWNiMTNhNw==

The full, signed request:

    GET /api/rest/training/9kw2lkaw HTTP/1.0
    Date: Sun, 1 Jan 2012 11:30:01 GMT
    Authorization: EMT 62CS22QCUpQ57CTG NTIwNjQzMTE2MDFlZGI4M2M4OTU5YjAyNTE2ZmIwMDg5NWNiMTNhNw==


### Example 2
We are signing the following request

    POST /api/rest/training/9kw2lkaw HTTP/1.0
    Content-Type: text/json
    Content-Md5: a7b591b4ea31f2fb9452370b690fdeac
    Date: Sun, 1 Jan 2012 11:30:01 GMT

    {name:"New name for training"}

Notice that we are using text/json as the body of this request, and we have already calculated an md5 sum
for it. To generate a signature for this request in in PHP 5.3 we would use the following code:

    <?php
    $verb = 'POST';    
    $url = '/api/rest/training/9kw2lkaw';
    $contentType = 'text/json';
    $contentMd5 = 'a7b591b4ea31f2fb9452370b690fdeac';
    $date = 'Sun, 1 Jan 2012 11:30:01 GMT';
    $keySecret = '7mq6gEEL3V1ijnUl7MbWSGix6s0I3NN7';
    $keyID = '62CS22QCUpQ57CTG';
        
    $authorization = 'EMT '.$keyID.' ';
    $authorization .= base64_encode(sha1( $verb.$url.$contentType.$contentMd5.$date.$keySecret ));
    echo $authorization;

The result:

    EMT 62CS22QCUpQ57CTG NzVhZTIyOWNlNDk0NmQ5ZGZjYTk4YTE0ZmM5ZWI3NTU1OTlmYTg4Mg==

The full, signed request:

    POST /api/rest/training/9kw2lkaw HTTP/1.0
    Content-Type: text/json
    Content-Md5: a7b591b4ea31f2fb9452370b690fdeac
    Date: Sun, 1 Jan 2012 11:30:01 GMT
    Authorization: EMT 62CS22QCUpQ57CTG NzVhZTIyOWNlNDk0NmQ5ZGZjYTk4YTE0ZmM5ZWI3NTU1OTlmYTg4Mg==[

    {name:"New name for training"}



RESTful API                                                                      <a name="rest"></a>
=============
This access method allows for easy manipulation of model objects. It follows [HTTP RESTful web interface][REST]
rules and provide all basic http verbs.

In order to access RESTful API you need to send http request to list or item url


### List URL                                                                <a name="rest1"></a>

List URL allows you to retrieve a list of items for the given model. For example, in order to retrieve a list of
trainings, you will send a GET request to `/api/rest/training`. 

List URL has the following format:

    /api/rest/[MODEL NAME]

### General list params                                                      <a name="rest2"></a>

You can use query params to change order, number of items or filter out the results. Params can be sent in GET
or POST and have to be url-encoded. Below is a list of available query parameters


|    Parameter    |   Format         |       Description                                                    |
| --------------- | ---------------- | -------------------------------------------------------------------- |
|   `format`      |   string         | Force output format. Can be one of: json, xml, php.                  |
|   `limit`       |   integer >= 0   | Limits the number of items returned.                                 |
|   `offset`      |   integer >= 0   | Used together with limit, returns items starting from n-th item.     |
|   `order`       |   string         | Order items by this field. Can be any field in selected model.       |
|   `orderDir`    |   ASC or DESC    | Order direction ascending or descending (respectively)               |


### Filtering params                                                         <a name="rest3"></a>

Filtering params can be used to perform simple queries to the database. If there are multiple params in a single
request they will be joined with `AND` operator (server will return items that match every parameters). 

A `FIELD` can be any field in current model.


|    Parameter         |   Operator          | Description                                                     |
| -------------------- | ------------------- | --------------------------------------------------------------- |
| filter-FIELD-eq      |  Equals to X        | Find all items that have FIELD equal to X
| filter-FIELD-ne      |  Not equals to X    | Find all items that have FIELD NOT equal to X
| filter-FIELD-gt      |  Greater than X     | Find all items that have FIELD greater than X
| filter-FIELD-gte     |  Greater or equal X | Find all items that have FIELD greater than or equal to X
| filter-FIELD-lt      |  Less than X        | Find all items that have FIELD less than X
| filter-FIELD-lte     |  Less or equal X    | Find all items that have FIELD less than or equal to X
| filter-FIELD-like    |  contains X         | Find all items with FIELD that contains X
| filter-FIELD-notLike |  does not contain X | Find all items with FIELD that DOES NOT contain X


### Item URL                                                                <a name="rest4"></a>

Item URL allows you to retrieve and manipulate a single item from a given model. 

Item URL has the following format:

    /api/rest/[MODEL NAME]/[ITEM ID]

### Item query methods

You can use the following HTTP methods to access the item.


|  HTTP method    |   Action         |       Description                                                    |
| --------------- | ---------------- | -------------------------------------------------------------------- |
|   GET           |   READ           | Retrieve information on a single item |
|   POST          |   UPDATE         | Update a single item with the supplied data                          |
|   PUT           |   CREATE         | Create new item with the supplied data     |
|   DELETE        |   DELETE         | Order items by this field. Can be any field in selected model.       |



### Changing query data format                                               <a name="rest5"></a>

`POST` and `PUT` queries require data to be sent in the request. 

In order to specify data format, use a standard `Content-Type` header. You can use any of the content types:

 * `Content-Type: text/json`
 * `Content-Type: application/json`
 * `Content-Type: text/xml`

> All strings have to be UTF-8 encoded. Integers can have maximum size of 64-bit.



### Changing query result format

API server can send back result in several formats. You can select result format by using `?format=` query param.

For example, to retrieve all data for training id `KK2J932kks` in `XML` format:

    GET /api/rest/training/KK2J932kks?format=xml

To list all orders in JSON format:

    GET /api/rest/training?format=json



### Models                                                                   <a name="rest7"></a>

eMentor API currently provides RESTful access to the following models:

 * `training` - products owned by you, having unique id, price, name etc.
 * `client` - clients that have bought 1 or more products from you, identified by id
 * `order` - orders created via API or placed in your dedicated store




Direct API                                                                       <a name="direct"></a>
=============



Reference
===========
[REST]:   <http://en.wikipedia.org/wiki/REST> "Representational state transfer"
[APanel]: <http://www.ementor.pl/panel> "Author's Panel - eMentor.pl"
[SHA1]:   <http://tools.ietf.org/html/rfc3174> "SHA1 RFC"

----
![](http://www.ementor.pl/img/logo-white.png)