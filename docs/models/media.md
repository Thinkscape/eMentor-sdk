eMentor API v2 documentation
--------------------------------

# "media" model #

Introduction
==============

A `media` object refers to a piece of video, audio or other file associated with a media.


Attributes
==============

  **Name**         | **Format**    | **Required** | **Access** | **Description**
------------------ | ------------- | ------------ | ---------- | --------------------
  `id`             | string        |              | read-only  | Unique identifier for this media
  `type`           | string        | **Required** | write once | Media type, can be assigned at creation time one of: video|file
  `productId`      | string        | **Required** | write once | Associated product id, can only be assigned during creation.
  `name`           | string        |              | write+read | Human-readable media name, visible to the user.
  `status`         | integer       |              | write+read | Current media status. For a list of available status values, see next section.
  `statusName`     | string        |              | read-only  | Media status name.
  `statusProgress` | integer       |              | read-only  | Current progress status - i.e. when status = ENCODING, status progress represents percent of encoding work performed.
  `dateCreated`    | integer       |              | read-only  | Date the media has been created in the database (unix timestamp, seconds since unix epoch)
  `dateCreatedIso` | string        |              | read-only  | Date the media has been created in the database (ISO 8601)
  `dateModified`   | integer       |              | read-only  | Date the media has been last modified (unix timestamp, seconds since unix epoch) or null if never modified.
  `dateModifiedIso`| string        |              | read-only  | Date the media has been last modified (ISO 8601) or null if never modified.
  `isPreview`      | boolean       |              | write once | Is this media a free (preview) media? False if it should only be accessible after media purchase
  `rawName`        | string        |              | read-only  | Raw (source) file name
  `rawLength`      | integer       |              | read-only  | Media length in seconds.


Media status
==============

**Warning!** Media status can only be changed when current status is `READY` or `SUSPENDED`.

 **Status value**   |  **Name**              | **Can be set?** | **Description**
 ------------------ | ---------------------- | --------------- | ---------------------------------------
 150                | READY_PROCESSING       | NO              | A new version of media is currently being processed
 120                | READY_PROCESSING_ERROR | NO              | A new version of media could not be processed.
 100                | READY                  | YES             | Ready and available for viewing.
 60                 | MODERATION             | NO              | Media is awaiting moderation.
 50                 | PROCESSED              | NO              | Media is processed but has not been marked as ready yet.
 40                 | PROCESSING             | NO              | Media is currently being prepared/encoded.
 0                  | MEDIA                  | NO              | New media awaiting processing.
 -15                | PROCESSING_ERROR       | NO              | There has been an error during media processing.
 -30                | REJECTED               | NO              | Rejected after moderation.
 -40                | SUSPENDED              | YES             | Cannot be viewed by users.
 -100               | DELETED                | YES             | Deleted and all media files removed from servers.

Associations
==============

### /product

 * **model**: [product](product.md)
 * **count**: 1
 * **access**: read-only

The product that this media belongs to.

For example, to retrieve product for media `Q0aLS6Kvz028VcJ` we could use the following REST request:

    GET /api/v2/rest/media/Q0aLS6Kvz028VcJ/product


### /embed

 * **model**: [mediaembed](mediaembed.md)
 * **count**: 1 or more
 * **access**: read-only

Media embed codes

For example, to retrieve embeds for media `Q0aLS6Kvz028VcJ` for user `user@email.com` we could use the following REST
request:

    GET /api/v2/rest/media/Q0aLS6Kvz028VcJ/embed?filter-userId-eq=user@email.com

**Warning** The `filter-userId-eq` parameter is mandatory in order to retrieve embeds. More information can be found in
[media embed model](mediaembed.md) documentation.


----
![](http://www.ementor.pl/img/logo-white.png)
