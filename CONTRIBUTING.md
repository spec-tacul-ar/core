# Contributing

Thank you for considering contributing to Spectacular!

Please open an issue or discussion before embarking on major changes or new features to see if they align with the project's long-term vision.

Contributors will be asked to sign our [Fiduciary License Agreement](https://fsfe.org/activities/fla/fla.en.html). This will guarantee that your contributions remain open forever while making maintenance of the project sustainable.

## Code of Conduct

The Spectacular code of conduct is derived from the Laravel code of conduct. Any violations of the code of conduct may be reported to Matthew White (matt@matthewwhite.me.uk):

* Participants will be tolerant of opposing views.
* Participants must ensure that their language and actions are free of personal attacks and disparaging personal remarks.
* When interpreting the words and actions of others, participants should always assume good intentions.
* Behavior that can be reasonably considered harassment will not be tolerated.

## Conventions
* Use alphabetical sorting where another doesn't make sense.
* snake_case for variables, camelCase for functions (unless a dependency demands otherwise).
* IDs are never zero.
* A summary field is plain text. A description field is rich text.
* Terminology regarding the Revisionable feature: Release = a snapshot of the specification, Revision = individual changes, Version = a moment in time
* When listing HTML attributes vertically, the last line has the closing bracket. Use a line break to signal the start of the content. No `>` on their own lines.
* HTML attribute order: element-specific (`type="text"`, `href="#top"`), id, class, booleans, Vue. The exception being `v-if`, `v-elseif` and `v-else` which must go first.

## API

The API keeps things simple. From the client's perspective:

* It only uses two HTTP methods:
    * **GET** Tell me something
    * **POST** Do something
* It follows BREAD naming:
    * **Browse** List the resource
    * **Read** Fetch a resource
    * **Edit** Change the resource
    * **Add** Create a new resource
    * **Delete** Delete a resource

## Endpoints

Most endpoints are [Cruddy by Design](https://www.youtube.com/watch?v=MF0jFKvS4SI). Or in our case, *Bready* because it includes a verb for the index endpoint. One difference is that we avoid using nested relations unless absolutely necessary (see exception below).

When not addressing a single entity, the first part of the endpoint defines the type of entity we want to retrieve or manipulate. The second is a verb describing what we want to do.

`templates/add`

Filtering and pagination are achieved with GET parameters. It's important to note that we still require the user's identifier in this example even though it can be fetched from the session. This is to ensure that endpoints are stateless for caching and that the session is only used for authorisation.

`projects/browse?user_id=123`

When we are dealing with a specific entity, we specify it after the entity type.

`comments/234/update`

Domain actions use a verb that describes the state transition. In the example below, we mark requirement 345 as complete.

`requirements/345/complete`
