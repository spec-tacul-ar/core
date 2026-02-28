# Contributing

> [!NOTE]
> *This file is still **Work In Progress** and may refer to many conventions that haven't even been implemented yet.*

Thank you for considering contributing to Spectacular!

Please open an issue or discussion before embarking on major changes or new features to see if they align with the goals of the project.

Contributors will be asked to sign our Fiduciary License Agreement. This ensures that development is sustainable while guaranteeing that your contributions remain open forever. 

## Code of Conduct

The Spectacular code of conduct is derived from the Laravel code of conduct. Any violations of the code of conduct may be reported to Matthew White (matt@matthewwhite.me.uk):

* Participants will be tolerant of opposing views.
* Participants must ensure that their language and actions are free of personal attacks and disparaging personal remarks.
* When interpreting the words and actions of others, participants should always assume good intentions.
* Behavior that can be reasonably considered harassment will not be tolerated.

## Code style

These are guidelines. Please conform to them where you can but don't sweat it.

### For PHP
* Adhere to PSR-12
* Middleware should be defined in the routes, not the controllers.
* Prefer direct calls over events. Use actions if called in more than one place. Use events only for DB trigger-like logic.

### For Javascript
* End statements with a semi colon.
* When listing HTML attributes vertically, the last line has the closing bracket. Use a line break to signal the start of the content. No `>` on their own lines.
* HTML attribute order: element-specific (`type="text"`, `href="#top"`), id, class, booleans, Vue. The exception being `v-if`, `v-elseif` and `v-else` which must go first.

### For both
* Four space indentation.
* End arrays and object fields with commas.
* Use alphabetical sorting where another doesn't make sense.
* snake_case for variables, camelCase for functions (unless a dependency demands otherwise).

## Other notes
* IDs are never zero.
* Summary fields are short plain text. Description is rich text.
* Regarding the Revisionable feature: Release = a snapshot of the specification, Revision = individual changes, Version = a moment in time

## API

The API keeps things simple. From the client's perspective:

* It only uses two HTTP methods:
    * **GET** Tell me something
    * **POST** Do something
* It only uses two HTTP error codes:
    * **400** You did something wrong
    * **500** Something went wrong our end
* It follows BREAD naming:
    * **Browse** List the resource
    * **Read** Fetch a resource
    * **Edit** Change the resource
    * **Add** Create a new resource
    * **Delete** Delete a resource
* It always returns an error code:
    * **0** Everything worked
    * **1** Unknown problem
    * **2** Validation error

## Endpoints

Most endpoints are [Cruddy by Design](https://www.youtube.com/watch?v=MF0jFKvS4SI). Or in our case, *bready* because it includes a verb for the index endpoint. One difference is that we avoid using nested relations unless absolutely necessary (see exception below).

When not addressing a single entity, the first part of the endpoint defines the type of entitiy we want to retrieve or manipulate. The second is a verb describing what we want to do.

`templates/add`

Filtering and pagination are achieve with GET parameters. It's important to note that we still require the user's identifier in this example even though it can be fetched from the session. This is to ensure that endpoints are stateless for caching and that the session is only used for authorisation.

`projects/browse?user_id=123`

When we are dealing with a specific entity, we specify it after the entity type.

`comments/234/update`

Sometimes we have to break convention when bulk actions would be inefficient, owing to the number of requests that would otherwise be needed. In the example below, we mark all tasks belonging to requirement 345 as complete.

`requirements/345/tasks/complete`

## Example responses

```
{
    "code": 0,
    "message": "OK",
    "payload": {
        ...
    }
}

```
The payload can be an object, array or null - depending on the nature of the request. Successful resource creation and modification operations will typically return the new resource as the payload.

Something akin to an HTTP 204 _No Content_ would look like this:

```
{
    "code": 0,
    "message": "Resource deleted.",
    "payload": null
}
```

Some errors can put further information into the payload.

```
{
    "code": 2,
    "message": "A validation error occured.",
    "payload": {
        "name": [
            "The name field must be at least two characters in length.",
            "The name field must not contain non-ASCII characters."
        ],
        "email": [
            "An email address is required."
        ]
    }
}
```
