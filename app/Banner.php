<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{

  protected $fillable=['image_path','publish_flag','teks','url_link','created_by','last_update_by'];
}
