<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model as BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

abstract class Model extends BaseModel
{
    use SoftDeletes;

    /**
     * Los atributos que son asignables masivamente.
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * Los atributos que deben ser ocultados para arrays.
     *
     * @var array
     */
    protected $hidden = [
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Los atributos que deben ser convertidos a fechas.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Los atributos que deben ser convertidos a booleanos.
     *
     * @var array
     */
    protected $booleans = [];

    /**
     * Los atributos que deben ser convertidos a enteros.
     *
     * @var array
     */
    protected $integers = [];

    /**
     * Los atributos que deben ser convertidos a flotantes.
     *
     * @var array
     */
    protected $floats = [];

    /**
     * Los atributos que deben ser convertidos a strings.
     *
     * @var array
     */
    protected $strings = [];

    /**
     * Boot el modelo.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->beforeCreate();
        });

        static::created(function ($model) {
            $model->afterCreate();
        });

        static::updating(function ($model) {
            $model->beforeUpdate();
        });

        static::updated(function ($model) {
            $model->afterUpdate();
        });

        static::deleting(function ($model) {
            $model->beforeDelete();
        });

        static::deleted(function ($model) {
            $model->afterDelete();
        });
    }

    /**
     * Hook antes de crear el modelo.
     *
     * @return void
     */
    protected function beforeCreate()
    {
        //
    }

    /**
     * Hook después de crear el modelo.
     *
     * @return void
     */
    protected function afterCreate()
    {
        //
    }

    /**
     * Hook antes de actualizar el modelo.
     *
     * @return void
     */
    protected function beforeUpdate()
    {
        //
    }

    /**
     * Hook después de actualizar el modelo.
     *
     * @return void
     */
    protected function afterUpdate()
    {
        //
    }

    /**
     * Hook antes de eliminar el modelo.
     *
     * @return void
     */
    protected function beforeDelete()
    {
        //
    }

    /**
     * Hook después de eliminar el modelo.
     *
     * @return void
     */
    protected function afterDelete()
    {
        //
    }

    /**
     * Obtiene el nombre de la tabla asociada al modelo.
     *
     * @return string
     */
    public function getTable()
    {
        return $this->table ?? strtolower(class_basename($this));
    }

    /**
     * Obtiene el nombre del modelo.
     *
     * @return string
     */
    public function getModelName()
    {
        return class_basename($this);
    }

    /**
     * Obtiene el nombre del modelo en plural.
     *
     * @return string
     */
    public function getModelNamePlural()
    {
        return Str::plural($this->getModelName());
    }

    /**
     * Obtiene el nombre del modelo en singular.
     *
     * @return string
     */
    public function getModelNameSingular()
    {
        return Str::singular($this->getModelName());
    }

    /**
     * Obtiene el nombre del modelo en minúsculas.
     *
     * @return string
     */
    public function getModelNameLower()
    {
        return strtolower($this->getModelName());
    }

    /**
     * Obtiene el nombre del modelo en minúsculas y plural.
     *
     * @return string
     */
    public function getModelNameLowerPlural()
    {
        return strtolower($this->getModelNamePlural());
    }

    /**
     * Obtiene el nombre del modelo en minúsculas y singular.
     *
     * @return string
     */
    public function getModelNameLowerSingular()
    {
        return strtolower($this->getModelNameSingular());
    }
}
