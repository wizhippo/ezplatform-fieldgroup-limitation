# ezplatform-fieldgroup-limitation
Repository dedicated to fieldgroup limitation for eZ Platform.

Limits AdminUI forms to fields the user has permission to.  Basic permission enforcement support enforced by
`BeforeCreateContentEvent` and `BeforeUpdateContentEvent` events that should catch is a user tries to use REST/graphql to
modify content.  REST/graphql read operations are not filtered on `Read`.

You can create policies like the below to limit field access:
```
Content Field / Create / Field Group: internal_use
Content Field / Edit / Field Group: internal_use
Content Field / Read / Field Group: internal_use
```

Make sure that if limitiing `Content Field / Create` the user has the ability to populate all manditory fields or an
exception will be thrown.

Content Field / Read / Field Group enforced only in forms.  Templates need to be modified to check if usr can read field
or not `canUser('content_field', 'read', $contentType, [$field])`
