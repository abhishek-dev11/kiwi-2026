<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class ProducSubtVariantItems extends Model
{
    use HasFactory;
    protected $table = "product_sub_variant_items";

    protected $fillable = [
        'product_id',
        'product_variant_id',
        'product_sub_variant_id',
        'sku_number',
        'stock',
        'price',
        'weight',
        'length',
        'width',
        'height',
        'status',
        'is_deleted',
    ];
    
}
