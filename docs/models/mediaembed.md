eMentor API v2 documentation
--------------------------------

# "mediaembed" model #

Introduction
==============

This model is used to access media embeds.

**Warning:** Each user (client) has a unique set of media embeds. Do not use the same embed code to display video to
different users. Always fetch up-to-date embeds for a particular user.

In order to retrieve media embeds for a paid media (not preview) you **must** supply two filter params:

 * `filter-mediaId-eq` - the unique id for the media you'd like to receive embeds for
 * `filter-userId-eq` - target user email

If any of those parameters is missing, the API server will return an error code.

To retrieve media embeds for free (preview) media, you just need one parameter:

 * `filter-mediaId-eq` - the unique id for the media you'd like to receive embeds for


Attributes
==============

  **Name**         | **Format**    | **Required** | **Access** | **Description**
------------------ | ------------- | ------------ | ---------- | --------------------
  `mediaId'        | string        |              | read-only  | Associated media id
  `userId`         | string        |              | read-only  | Associated user id (email address)
  `template`       | string        |              | read-only  | Template name (i.e. 'basic')
  `embed`          | string (HTML) |              | read-only  | HTML code for the embed.

Available templates
==============

 * `basic` - 439x332px, JS-based player
 * `medium250` - skinnable, 262x197px, JS-based player
 * `clearWidescreen` - skinnable, 568x320px, JS-based player
 * `html5video` - html5 `<video>` element, stylable, 568x320px

You can customize embeds using standard CSS. All embeds use `eMentorThumb` class and contain thumbnail picture fetched
directly from API server.

Examples
==============

For example, to retrieve embeds for media `Q0aLS6Kvz028VcJ` we could use the following REST request:

    GET /api/v2/rest/mediaembed/?filter-userId-eq=user@email.com&filter-mediaId=Q0aLS6Kvz028VcJ

We can also use media association, like this:

    GET /api/v2/rest/mediaembed/Q0aLS6Kvz028VcJ/embed?filter-userId-eq=user@email.com

The result will be similar to:

    [
        {
            template: "basic",
            userId: "user@email.com",
            mediaId: "Q0aLS6Kvz028VcJ",
            embed: "<script type=\"text/javascript\" src=\"http://www.ementor.pl/embed/media/Q0aLS6Kvz028VcJ/basic?userId=user@email.com&keyId=12345678&signature=70Q5PDzPxQ73N11e0Rmb0cqRHzJmdUwd"></script>",
        },
        {
            template: "clearWidescreen",
            userId: "user@email.com",
            mediaId: "Q0aLS6Kvz028VcJ",
            embed: "<script type=\"text/javascript\" src=\"http://www.ementor.pl/embed/media/Q0aLS6Kvz028VcJ/clearWidescreen?userId=user@email.com&keyId=12345678&signature=70Q5PDzPxQ73N11e0Rmb0cqRHzJmdUwd"></script>",
        }
    ]

----
![](http://www.ementor.pl/img/logo-white.png)
