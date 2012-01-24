eMentor API v2 documentation
--------------------------------
 
 1. [Technical overview]                    (#overview)
 2. [Signing requests]                      (#signing)
 3. [RESTful API]                           (#rest)
   1. [Listing objects]                     (#rest1)
     1. [List URL]                          (#rest11)
     2. [General list params]               (#rest12)
     3. [Filtering params]                  (#rest13)  
   2. [Fetching and manipulating objects]   (#rest2)
     1. [Item URL]                          (#rest21)
     2. [Item query methods]                (#rest22)
     3. [Request format]                    (#rest23)
     4. [Response format]                   (#rest24)
   3. [Models]                              (#rest3)  
  
 4. [Direct API]                            (#direct)
     1. [Request format]                    (#direct1)
     2. [Result format]                     (#direct2)
     3. [Error response]                    (#direct3)
     4. [Success response]                  (#direct4)
     5. [Examples]                          (#direct5)
     6. [Methods]                           (#direct6)


Technical overview                                                               <a name="overview"></a>
======================
eMentor system can be accessed via [RESTful API](#rest) and [Direct API](#direct). All connections to the API server
are made via HTTPS (port 443) on a dedicated API URL. RESTful and Direct methods are complementary. As a general rule,
you should use REST for obtaining and manipulating object data and Direct API for all other operations not covered by
REST.

All requests to the API are signed with unique API Key, as [described in the next chapter](#signing). You can obtain 
it by logging to [Author's Panel][APanel] with your email and password.

API server can return response in [several formats](#rest24), including:

 * JSON
 * XML (schema-less)
 * PHP serialized associative array (`serialize(array())`)

When updating objects in database, [API server can digest](#rest23) the following formats:

 * POST params (form-data)
 * JSON (text/json)
 * XML (text/xml)
    
    
Signing requests                                                                 <a name="signing"></a>
======================
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

    POST /api/v2/rest/training/9kw2lkaw HTTP/1.0
    Content-Md5: c8fdb181845a4ca6b8fec737b3581d76
    Content-Type: text/html
    Date: Sun, 1 Jan 2012 11:30:01 GMT
    Authorization: EMT 62CS22QCUpQ57CTG MWEwODJiN2QyNzQ4MjlmMWY3MWRkZmY4MjJhNGJhYWY4YWY5OWIxYQ==



## Authorization Header

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

```php    
    <?php
    $checksumString = $httpVerb . $url . $contentType. $contentMd5 . $date . $keySecret;
    $signature = base64_encode( sha1( $checksumString ) );
```



## Example 1
Assuming our Key ID is `62CS22QCUpQ57CTG` and Key Secret is `7mq6gEEL3V1ijnUl7MbWSGix6s0I3NN7` we are signing
the following request

    GET /api/v2/rest/training/9kw2lkaw HTTP/1.0
    Date: Sun, 1 Jan 2012 11:30:01 GMT

In this example, there are no `Content-Type` or `Content-Md5` headers, so we will calculate a signature using only
HTTP verb, date and key secret. For example, in PHP 5.3 we would use the following code:

```php
    <?php
    $verb = 'GET';    
    $url = '/api/v2/rest/training/9kw2lkaw';
    $date = 'Sun, 1 Jan 2012 11:30:01 GMT';
    $keySecret = '7mq6gEEL3V1ijnUl7MbWSGix6s0I3NN7';
    $keyID = '62CS22QCUpQ57CTG';
        
    $authorization = 'EMT '. $keyID . ' ' .base64_encode(sha1( $verb . $url . $date . $keySecret ));
    echo $authorization;
```


The result:

    EMT 62CS22QCUpQ57CTG NTIwNjQzMTE2MDFlZGI4M2M4OTU5YjAyNTE2ZmIwMDg5NWNiMTNhNw==

The full, signed request:

    GET /api/v2/rest/training/9kw2lkaw HTTP/1.0
    Date: Sun, 1 Jan 2012 11:30:01 GMT
    Authorization: EMT 62CS22QCUpQ57CTG NTIwNjQzMTE2MDFlZGI4M2M4OTU5YjAyNTE2ZmIwMDg5NWNiMTNhNw==


## Example 2
We are signing the following request

    POST /api/v2/rest/training/9kw2lkaw HTTP/1.0
    Content-Type: text/json
    Content-Md5: a7b591b4ea31f2fb9452370b690fdeac
    Date: Sun, 1 Jan 2012 11:30:01 GMT

    {name:"New name for training"}

Notice that we are using text/json as the body of this request, and we have already calculated an md5 sum
for it. To generate a signature for this request in in PHP 5.3 we would use the following code:

```php
    <?php
    $verb = 'POST';    
    $url = '/api/v2/rest/training/9kw2lkaw';
    $contentType = 'text/json';
    $contentMd5 = 'a7b591b4ea31f2fb9452370b690fdeac';
    $date = 'Sun, 1 Jan 2012 11:30:01 GMT';
    $keySecret = '7mq6gEEL3V1ijnUl7MbWSGix6s0I3NN7';
    $keyID = '62CS22QCUpQ57CTG';
        
    $authorization = 'EMT '.$keyID.' ';
    $authorization .= base64_encode(sha1( $verb.$url.$contentType.$contentMd5.$date.$keySecret ));
    echo $authorization;
```

The result:

    EMT 62CS22QCUpQ57CTG NzVhZTIyOWNlNDk0NmQ5ZGZjYTk4YTE0ZmM5ZWI3NTU1OTlmYTg4Mg==

A signed request:

    POST /api/v2/rest/training/9kw2lkaw HTTP/1.0
    Content-Type: text/json
    Content-Md5: a7b591b4ea31f2fb9452370b690fdeac
    Date: Sun, 1 Jan 2012 11:30:01 GMT
    Authorization: EMT 62CS22QCUpQ57CTG NzVhZTIyOWNlNDk0NmQ5ZGZjYTk4YTE0ZmM5ZWI3NTU1OTlmYTg4Mg==

    {name:"New name for training"}


-----


RESTful API                                                                             <a name="rest"></a>
=======================================================================================================================
This access method allows for easy manipulation of model objects. It follows [HTTP RESTful web interface][REST]
rules and provide all basic http verbs.

In order to access RESTful API you need to send http request to list or item url

## Request format                                                           <a name="rest23"></a>

`POST` and `PUT` queries require data to be sent in the request.

In order to specify request data format, use a standard `Content-Type` header. You can use any of the content types:

 * `Content-Type: text/json`
 * `Content-Type: application/json`
 * `Content-Type: text/xml`
 * `Content-Type: application/x-www-form-urlencoded`
 * `Content-Type: multipart/form-data`

When sending data to API server you have to follow the following rules:

 * all strings must be UTF-8 encoded
 * all integers and floats can have maximum resolution of 64-bit.
 * attribute names are case-sensitive
 * currencies are expressed as floats
 * when using XML:
   * name of the root node is ignored
   * values are held in separate nodes (all attributes are ignored)
   * long text nodes should be enclosed in `CDATA`


## Response format                                                          <a name="rest24"></a>

API server can send back result in several formats. You can select result format by using `?format=` query param.
By default, API Server will use the same format as query (if any) or JSON. Responses are always UTF-8 encoded and
use ANSI C (US/western) locale for numeric values.

Currently supported formats are:

 * `format=json` - [JSON][]
 * `format=xml` - schema-less XML, with values in separate nodes. Items are stored in `<item>` nodes
 * `format=php` - php-compatible [serialization][php-serialize] of nested objects (associative array)
 * `format=post` - url-encoded values ([RFC1738][RFC1738])
 * `format=urlencode` - url-encoded values ([RFC1738][RFC1738])

 For example, to retrieve all data for training id `KK2J932kks` in `XML` format:

    GET /api/v2/rest/training/KK2J932kks?format=xml

I.e. to list all orders in JSON format:

    GET /api/v2/rest/order?format=json



## Models                                                                   <a name="rest3"></a>

eMentor API currently provides RESTful access to the following models:

 * [product](models/product.md) - products owned by you, having unique id, price, name etc.
 * [media](models/media.md) - each product has 1 or more media (video, audio, archive etc.)
 * [user](models/user.md) - clients, identified by email address
 * [order](models/order.md) - orders created via API or placed in your dedicated store
 * [orderitem](models/orderitem.md) - each order contains 1 or more items. Each item relates to a product

All models are described in detail in [separate documentation files](models/).


------------------------------------------------------------------------------------------------------------------
## Listing objects                                                          <a name="rest1"></a>

### List URL                                                                <a name="rest11"></a>

List URL allows you to retrieve a list of items for the given model. For example, in order to retrieve a list of
trainings, you will send a GET request to `/api/v2/rest/training`. 

List URL has the following format:

    /api/v2/rest/[MODEL NAME]

It supports the following HTTP methods:

|  HTTP method    |   Action         |       Description                                                    |
| --------------- | ---------------- | -------------------------------------------------------------------- |
|   GET           |   LIST           | Retrieve a list of items from the specified model
|   POST          |   CREATE         | Create a new item with the supplied data



### General list params                                                      <a name="rest12"></a>

You can use query params to change order, number of items or filter out the results. Params can be sent in GET
or POST and have to be url-encoded. Below is a list of available query parameters

|    Parameter    |   Format         |       Description                                                    |
| --------------- | ---------------- | -------------------------------------------------------------------- |
|   `format`      |   string         | Force output format. Can be one of: json, xml, php.                  |
|   `limit`       |   integer >= 0   | Limits the number of items returned. The maximum and default is 100  |
|   `offset`      |   integer >= 0   | Used together with limit, returns items starting from n-th item.     |
|   `order`       |   string         | Order items by this field. Can be any field in selected model.       |
|   `orderDir`    |   ASC or DESC    | Order direction ascending or descending (respectively)               |


### Filtering params                                                         <a name="rest13"></a>

Filtering params can be used to perform simple queries to the database. If there are multiple params in a single
request they will be joined with `AND` operator (server will return items that match every parameters). 

A `FIELD` can be any field in current model.


|    Parameter           |   Operator          | Description                                                     |
| ---------------------- | ------------------- | --------------------------------------------------------------- |
| `filter-FIELD-eq`      |  Equals to X        | Find all items that have FIELD equal to X
| `filter-FIELD-ne`      |  Not equals to X    | Find all items that have FIELD NOT equal to X
| `filter-FIELD-gt`      |  Greater than X     | Find all items that have FIELD greater than X
| `filter-FIELD-gte`     |  Greater or equal X | Find all items that have FIELD greater than or equal to X
| `filter-FIELD-lt`      |  Less than X        | Find all items that have FIELD less than X
| `filter-FIELD-lte`     |  Less or equal X    | Find all items that have FIELD less than or equal to X
| `filter-FIELD-like`    |  contains X         | Find all items with FIELD that contains X
| `filter-FIELD-notLike` |  does not contain X | Find all items with FIELD that DOES NOT contain X

------------------------------------------------------------------------------------------------------------------
## Creating new items

In order to create a new item, you need to send `POST` request to the list url.

For example, to add new `training` object we could send the following request

    POST /api/v2/rest/training  HTTP 1.0
    Date: Wed, 4 Jan 2012 20:30:12 GMT
    Content-Type: text/json
    Content-Md5: 1b46db44faefafcc49b1e8fa6847d1ad
    Authorization: EMT 9kCUel19OEEWG110 F4DLKKdjkad92WW93dk19dsad12dsa221KkdlsloorEOO232139AAA==

    {name:"My new training ",price:19.99}

------------------------------------------------------------------------------------------------------------------
## Fetching and manipulating objects                                        <a name="rest2"></a>

### Item URL                                                               <a name="rest21"></a>

Item URL allows you to retrieve and manipulate a single item from a given model. 

Item URL has the following format:

    /api/v2/rest/[MODEL NAME]/[ITEM ID]

  
### Item query methods                                                     <a name="rest22"></a>

You can use the following HTTP methods to access the item.

|  HTTP method    |   Action         |       Description                                                    |
| --------------- | ---------------- | -------------------------------------------------------------------- |
|   GET           |   READ           | Retrieve information on a single item 
|   POST          |   UPDATE         | Update a single item with the supplied data                          
|   PUT           |   UPDATE         | Update a single item with the supplied data
|   DELETE        |   DELETE         | Permanently delete this item.                                        


For example, to delete training with id `ii2kw882` we could the following request:

    DELETE /api/v2/rest/training/ii2kw882 HTTP/1.0
    Date: Sun, 2 Jan 2012 15:11:52 GMT
    Authorization: EMT 9kCUel19OEEWG110 MWEwOOodekale9294jhdsa738123dmY4MjJhNGJhYWY4YWY5OWIxYQ==

To change its price we could the following request:

    POST /api/v2/rest/training/ii2kw882 HTTP/1.0
    Date: Sun, 2 Jan 2012 15:11:52 GMT
    Content-Type: text/json
    Content-Md5: 79de1cf4c48de6229988458f9b3a9798
    Authorization: EMT 9kCUel19OEEWG110 02llKKdjkad92lkD02hdsa738123dm132KkdlsloorEOOAP23C9A9W==

    {price:95.20}


------------------------------------------------------------------------------------------------------------------
Direct API                                                                   <a name="direct"></a>
=============

Direct API uses [JSON-RPC 2.0][JSONRPC] style of communication. It contains methods that are not always object-related
and are not suitable for REST access style.

## Request format                                                            <a name="direct1"></a>

Each request must contain an object with the following attributes:

 * `method` - **required** - full method name
 * `params` - optional - an object with params for the method. Can be ommited if the method allows it.
 * `id` - optional - request id

Each request must be [signed with a valid key][#signing], or it will be rejected by the server.

## Response format                                                           <a name="direct2"></a>

API server can send back result in several formats. You can select result format by using `?format=` query param.
By default, API Server will use the same format as query (if any) or JSON.

## Error response                                                            <a name="direct3"></a>

In case of an error, API Server will send back an object with the following attributes:

 * `id` - optional, request id
 * `error` - object with the following attributes
    * `code` - error code, integer
    * `message` - error message in English
    * `data` - optional, additional information

For example, in `JSON` format an error response could look like this:

    {error:{code:-32602,message:"Invalid parameters",data:"A required parameter ID is missing"}}

## Success response                                                          <a name="direct4"></a>

If the request was successful, the server will return an object with the following attributes:

 * `id` - optional, request id
 * `result` - method result, can be a primitive or an object

For example, in `JSON` format an error response could look like this:

    {id:'2011323',result:true}


## Examples                                                                  <a name="direct5"></a>

## Methods                                                                   <a name="direct6"></a>



Reference
===========
[REST]:             <http://en.wikipedia.org/wiki/REST> "Representational state transfer"
[JSONRPC]:          <http://json-rpc.org/wiki/specification> "JSON RPC Specification"
[JSON]:             <http://www.json.org/> "JSON Specification"
[APanel]:           <http://www.ementor.pl/panel> "Author's Panel - eMentor.pl"
[SHA1]:             <http://tools.ietf.org/html/rfc3174> "SHA1 RFC"
[php-serialize]:    <http://www.php.net/manual/en/function.serialize.php> "PHP serialize() function"
[RFC1738]:          <http://www.faqs.org/rfcs/rfc1738> "RFC 1738"
----
![](http://www.ementor.pl/img/logo-white.png)
