<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

trait CompanyScoped
{
    protected static function bootCompanyScoped()
    {
        // Escopo global para id_company
        static::addGlobalScope('company', function (Builder $query) {
            if (Auth::check()) {
                $query->where('id_company', Auth::user()->id_company);
            }
        });

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
                $model->saveQuietly();
            }
        });
    }
}
