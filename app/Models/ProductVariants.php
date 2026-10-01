<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ProductVariantImages;

class ProductVariants extends Model
{
    use HasFactory;
    protected $table = "product_variants";

    protected $fillable = [
        'product_id',
        'attribute_id',
        'value',
        'status',
        'is_deleted',
    ];

    public function images()
    {
        return $this->hasMany(ProductVariantImages::class, 'product_variant_id');
    }
}
