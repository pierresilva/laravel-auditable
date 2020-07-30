## Laravel Auditable

Easily track any changes to an eloquent model.

### Installation

```
composer require pierresilva/laravel-auditable
```

Insert the Revision service provider inside your `config/app.php` file:

```php
pierresilva\Auditable\AuditableServiceProvider::class,
```

Run 

```php arisan vendor:publish --"pierresilva\Auditable\AuditableServiceProvider"``` 

that publish the Auditable migration file. 

Then, run `php artisan migrate`.

You're all set!

### Setup

Insert the `pierresilva\Auditable\Traits\HasAuditsTrait` onto your
model that you'd like to track changes on:

```php
namespace App;

use pierresilva\Auditable\Traits\HasAuditsTrait;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable, HasAuditsTrait;
    
    /**
     * The morphMany revisions relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function audits()
    {
        return $this->morphMany(\pierresilva\Auditable\Models\Auditable::class, 'auditable');
    }
    
    /**
     * The current users ID for storage in revisions.
     *
     * @return int|string
     */
    public function auditUserId()
    {
        return auth()->id();
    }
}
```

### Usage

#### Audit Columns

You **must** insert the `$auditColumns` property on your model to track audits.

###### Simple log

```php
\pierresilva\Auditable\Auditable::log('User try to log in.');
```

###### Tracking All Columns

To track all changes on every column on the models database table, use an asterisk like so:

```php
use pierresilva\Auditable\Traits\HasAuditsTrait;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable, HasAuditsTrait;
    
    /**
     * The columns to keep audits of.
     *
     * @var array
     */
    protected $auditColumns = ['*'];
}
```

###### Tracking Specific Columns

To track changes on specific columns, insert the column names you'd like to track like so:

```php
class User extends Authenticatable
{
    use Notifiable, HasAuditsTrait;
    
    /**
     * The columns to keep audits of.
     *
     * @var array
     */
    protected $auditColumns = [
        'user_id',
        'title', 
        'description',
    ];
}
```

#### Set message audit key

To save a personalized message key do it as fallows

```php
    $user = User::findOrFail($userId);
    $user->auditKey = 'User edited by ' . auth()->user()->name;
    $user->name = $request->get('username');
    $user->save();
```

#### Get Audits

To get your audits on a record, call the relationship accessor `audits`. Remember, this is just
a regular Laravel relationship, so you can eager load / lazy load your revisions as you please:

```php
$user = User::with('audits')->find(1);

return view('user.show', ['user' => $user]);
```

On each audit record, you can use the following methods to display the revised data:

###### getUserResponsible()

To retrieve the User that performed the revision, use the method `getUserResponsible()`:

```php
$audit = Auditable::find(1);

$user = $audit->getUserResponsible(); // Returns user model

echo $user->id;
echo $user->email;
echo $user->first_name;
```

###### getOldValue()

To retrieve the old value of the record, use the method `getOldValue()`:

```php
$audit = Auditable::find(1);

echo $audit->getOldValue(); // Returns string
```

###### getNewValue()

To retrieve the new value of the record, use the method `getNewValue()`:

```php
$audit = Auditable::find(1);

echo $audit->getNewValue(); // Returns string
```

###### Example

```html
// In your `post.show` view:

@if($post->revisions->count() > 0)
    
     <table class="table table-striped">
     
        <thead>
            <tr>
                <th>User Responsible</th>
                <th>Message</th>
                <th>From</th>
                <th>To</th>
                <th>On</th>
            </tr>
        </thead>
        
        <tbody>
        
        @foreach($post->audits as $audit)
        
            <tr>
            
                <td>
                    {{ $audit->getUserResponsible()->first_name }}
                    
                    {{ $audit->getUserResponsible()->last_name }}
                </td>
                
                <td>{{ $audit->key() }}</td>
                
                <td>
                    @if(is_null($audit->old_value))
                        <em>None</em>
                    @else
                        {{ $audit->old_value }}
                    @endif
                </td>
                
                <td>{{ $audit->new_value }}</td>
                
                <td>{{ $audit->created_at }}</td>
                
            </tr>
            
        @endforeach
        
        </tbody>
            
    </table>
@else
    <h5>There are no audits to show.</h5>
@endif
```

#### Modifying the display of column names

To change the display of your column name that has been revised, insert the property `$auditColumnsFormatted` on your model:

```php
/**
 * The formatted revised column names.
 *
 * @var array
 */
protected $auditColumnsFormatted = [
    'user_id' => 'User',
    'title' => 'Post Title',
    'description' => 'Post Description',
];
```

#### Modifying the display of values

To change the display of your values that have been audited, insert the property `$auditColumnsMean`. You can use
dot notation syntax to indicate relationship values. For example:

```php
/**
 * The formatted revised column names.
 *
 * @var array
 */
protected $auditColumnsMean = [
    'user_id' => 'user.full_name',
];
```

You can even use laravel accessors with the `auditColumnsMean` property.

> **Note**: The audited value will be passed into the first parameter of the accessor.

```php
protected $auditColumnsMean = [
    'status' => 'status_label',
];

public function getStatusLabelAttribute($status = null)
{
    if(! $status) {
        $status = $this->getAttribute('status');
    }
    
    return view('status.label', ['status' => $status])->render();
}
```
