<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class RosterImport extends Model
{
    protected $table = 'roster_imports';
    protected $fillable = ['property_code','file_path','uploaded_by','total_rows','processed_count','error_count','errors'];
    protected $casts = ['errors' => 'array'];
}
