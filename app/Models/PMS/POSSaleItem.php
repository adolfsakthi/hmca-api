<?php

namespace App\Models\PMS;

use App\Models\PMS\POSItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class POSSaleItem extends Model
{
    use HasFactory;
    protected $fillable = ['pos_sale_id','pos_item_id','name','unit_price','quantity','line_total'];
    public function posItem() { return $this->belongsTo(POSItem::class,'pos_item_id'); }
}
