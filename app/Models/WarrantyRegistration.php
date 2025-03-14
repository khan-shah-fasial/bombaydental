<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarrantyRegistration extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'serial_no', 'bill_image', 'date_of_purchase', 'status'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
