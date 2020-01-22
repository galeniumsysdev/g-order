<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrgStructurePsc extends Model
{
    protected $table = 'org_structure_psc';
    protected $primaryKey = 'kode';
    protected $fillable = [
        'kode','parent','jenis','jabatan'
    ];

    public function hasParent()
    {
      return $this->belongsTo('App\OrgStructurePsc','parent');
    }

    public function hasChild()
    {
      return $this->hasMany('App\OrgStructurePsc','parent');
    }
}
