<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class ProductSubVariants extends Model
{
    use HasFactory;
    protected $table = "product_sub_variants";

    protected $fillable = [
        'product_id',
        'product_variant_id',
        'value',
        'status',
        'is_deleted',
    ];
}
