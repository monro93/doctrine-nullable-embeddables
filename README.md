#What

The purpose of this library is to fix a [known bug on doctrine](https://github.com/doctrine/orm/issues/4568) where you cannot have nullable embeddables.

The issue is the following:

>  Given a class "A" that has an ORM relation with an embedded "B" that could be null  
  When an instance of "A" is loaded from DB and "B" is null   
  Then an instance of "B" is created with all the values to null and assigned to the property "B" of "A"
  
The behaviour of this library will be the following:    

>Given a class "A" that has an ORM relation with an embedded "B" that could be null  
  When an instance of "A" is loaded from DB and "B" is null   
  Then the property "B" of "A" is set to null


For now it only works with yml files as is what we use, if you think that will be useful to have it for XML or annotations, please [open an issue](https://github.com/monro93/doctrine-nullable-embeddables/issues/new)
  
 #Installation
 This library is mainly though to be used with symfony as it depends on its configuration files.
 ## Syfmony
In your services.yaml (normally located at /config/) Add this line:
```yaml
imports:
    - { resource: ../vendors/EmbeNulls/config/services.yaml }
```
And if you need to change the doctrine.yaml location from the default one (`%kernel.project_dir%/config/packages/doctrine.yaml`), you can set the env variable `DOCTRINE_CONFIG_FILE`in your .env

#Usage
Define your orm mappings as usual in your YML files and on the embedded property add nullable to true wherever you want.
For instance:
```yaml
#Dog.orm.yml
Some\Namespace\Dog:
    type: entity
    table: dog
    id: ...
    fields: 
        name:
            type: string
    
    embedded:
        petIdentificationNumber:
            class: Some\Namespace\PetIdentificationNumber
            nullable: true

```