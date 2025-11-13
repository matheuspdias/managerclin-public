<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

abstract class BaseModel extends Model
{
    use SoftDeletes;

    protected static function booted()
    {
        // Escopo global para filtrar por id_company
        static::addGlobalScope('company', function ($query) {
            if (Auth::check()) {
                $query->where('id_company', Auth::user()->id_company);
            }
        });

        // Ações de criar/atualizar/excluir
        static::creating(function ($model) {
            if (Auth::check()) {
                $model->id_company = Auth::user()->id_company;
                $model->created_by = Auth::id();
                $model->updated_by = Auth::id();
            }
        });

        static::updating(function ($model) {
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }
        });

        static::deleting(function ($model) {
            if (Auth::check()) {
                $model->deleted_by = Auth::id();
                $model->save(); // Para persistir o `deleted_by`
            }
        });
    }
}
