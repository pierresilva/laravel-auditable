<?php

namespace pierresilva\Auditable\Traits;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;

trait HasAuditsTrait
{

    /**
     * Key message to identify the log
     * @var null | string
     */
    public $auditKey = null;

    private $olds = [];
    private $news = [];

    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    abstract public function getTable();

    /**
     * The morphMany audits relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    abstract public function audits();

    /**
     * The current users ID for storage in revisions.
     *
     * @return int|string
     */
    abstract public function auditUserId();

    /**
     * The trait boot method.
     *
     * @return void
     */
    public static function bootHasAuditsTrait()
    {
        self::created(function (Model $model) {
            $model->afterCreate();
        });

        self::updated(function (Model $model) {
            $model->afterUpdate();
        });

        self::deleted(function (Model $model) {
            $model->afterDelete();
        });
    }


    /**
     * Creates a revision record on the models save event.
     *
     * @return void
     */
    public function afterCreate()
    {
        if ($this->isDirty()) {
            array_map(function ($column) {
                if ($this->isDirty($column)) {
                    $this->olds[] = [
                        'column' => $column,
                        'value' => $this->getOriginal($column)
                    ];
                    $this->news[] = [
                        'column' => $column,
                        'value' => $this->getAttribute($column)
                    ];
                }
            }, $this->getAuditColumns());

            $this->processCreateAuditRecord(
                $this->auditKey ?? 'created',
                null,
                json_encode($this->news, JSON_PRETTY_PRINT)
            );
        }

        $this->olds = [];
        $this->news = [];
    }

    /**
     * Creates a revision record on the models save event.
     *
     * @return void
     */
    public function afterDelete()
    {
        if ($this->isDirty()) {
            array_map(function ($column) {
                if ($this->isDirty($column)) {
                    $this->olds[] = [
                        'column' => $column,
                        'value' => $this->getOriginal($column)
                    ];
                    $this->news[] = [
                        'column' => $column,
                        'value' => $this->getAttribute($column)
                    ];
                }
            }, $this->getAuditColumns());

            $this->processCreateAuditRecord(
                $this->auditKey ?? 'deleted',
                $this->olds ? json_encode($this->olds, JSON_PRETTY_PRINT) : null,
                $this->news ? json_encode($this->news, JSON_PRETTY_PRINT) : null
            );
        } else {

            array_map(function ($column) {
                $this->olds[] = [
                    'column' => $column,
                    'value' => $this->getOriginal($column)
                ];
                $this->news[] = [
                    'column' => $column,
                    'value' => $this->getAttribute($column)
                ];
            }, $this->getAuditColumns());

            $this->processCreateAuditRecord(
                $this->auditKey ?? 'deleted',
                json_encode($this->olds, JSON_PRETTY_PRINT),
                null
            );
        }

        $this->olds = [];
        $this->news = [];
    }

    /**
     * Creates a revision record on the models save event.
     *
     * @return void
     */
    public function afterUpdate()
    {
        if ($this->isDirty()) {
            array_map(function ($column) {
                if ($this->isDirty($column)) {
                    $this->olds[] = [
                        'column' => $column,
                        'value' => $this->getOriginal($column)
                    ];
                    $this->news[] = [
                        'column' => $column,
                        'value' => $this->getAttribute($column)
                    ];
                }
            }, $this->getAuditColumns());

            $this->processCreateAuditRecord(
                $this->auditKey ?? 'updated',
                $this->olds ? json_encode($this->olds, JSON_PRETTY_PRINT) : null,
                $this->news ? json_encode($this->news, JSON_PRETTY_PRINT) : null
            );
        }

        $this->olds = [];
        $this->news = [];
    }

    /**
     * Returns the audit columns formatted array.
     *
     * @return null|array
     */
    public function getAuditColumnsFormatted()
    {
        return $this->auditColumnsFormatted;
    }

    /**
     * Returns the audit columns mean array.
     *
     * @return null|array
     */
    public function getAuditColumnsMean()
    {
        return $this->auditColumnsMean;
    }

    /**
     * Sets the audit columns.
     *
     * @param array $columns
     *
     * @return $this
     */
    public function setAuditColumns(array $columns = ['*'])
    {
        if (property_exists($this, 'auditColumns')) {
            $this->auditColumns = $columns;
        }

        return $this;
    }

    /**
     * Sets the audit columns to avoid.
     *
     * @param array $columns
     *
     * @return $this
     */
    public function setAuditColumnsToAvoid(array $columns = [])
    {
        if (property_exists($this, 'auditColumnsToAvoid')) {
            // We'll check if the property exists so we don't assign
            // a non-existent column on the revision model.
            $this->auditColumnsToAvoid = $columns;
        }

        return $this;
    }

    /**
     * Returns the audit columns.
     *
     * @return array
     */
    protected function getAuditColumns()
    {
        $columns = is_array($this->auditColumns) ? $this->auditColumns : [];

        if (isset($columns[0]) && $columns[0] === '*') {
            // If we're given a wildcard, we'll retrieve
            // all columns to create revisions on.
            $columns = Schema::getColumnListing($this->getTable());
        }

        // Filter the returned columns by the columns to avoid.
        return array_filter($columns, function ($column) {
            $columnsToAvoid = is_array($this->auditColumnsToAvoid) ?
                $this->auditColumnsToAvoid : [];

            return !in_array($column, $columnsToAvoid);
        });
    }

    /**
     * Creates a new revision record.
     *
     * @param string|int $key
     * @param mixed $old
     * @param mixed $new
     *
     * @return Model
     */
    protected function processCreateAuditRecord($key, $old, $new)
    {
        $attributes = [
            'auditable_type' => self::class,
            'auditable_id' => $this->getKey(),
            'user_id' => $this->auditUserId(),
            'key' => $key,
            'old_value' => $old,
            'new_value' => $new,
        ];

        $model = $this->audits()
            ->getRelated()
            ->newInstance()
            ->forceFill($attributes);

        $model->save();

        return $model;
    }
}
