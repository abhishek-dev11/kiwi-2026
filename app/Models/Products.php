<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Categories;
use App\Models\ProducSubtVariantItems;


class Products extends Model
{
    use HasFactory;
    protected $table = "products";

    protected $fillable = [
        'title',
        'slug',
        'description',
        'short_decripation',
        'supplier_id',
        'product_type',
        'regular_price',
        'discount_price',
        'quantity',
        'weight',
        'length',
        'width',
        'height',
        'image',
        'status',
        'sku',
        'attribute_id',
        'sub_variant_attribute_id',
        'top_level_category_id',
        'mid_level_category_id',
        'bottom_level_category_id',
        'category',
        'is_popular',
        'is_delete',
    ];

    public function categories()
    {
        return $this->belongsToMany(Categories::class, 'category_product', 'product_id', 'category_id');
    }

    public function subVariantItems()
    {
        return $this->hasMany(ProducSubtVariantItems::class, 'product_id', 'id')
                    ->where('status', 'active'); // or ->where('is_deleted', 0)
    }
}
