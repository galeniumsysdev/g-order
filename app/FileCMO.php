<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FileCMO extends Model
{
  protected $table = "files_cmo";

  public $fillable = ['distributor_id','version','period','tahun','bulan','file_pdf','file_excel','first_download','approve','keterangan'];
  public function getdistributor()
  {
      return $this->belongsTo('App\Customer','distributor_id','id');
  }
}
