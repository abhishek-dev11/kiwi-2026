<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categories extends Model
{

    use HasFactory;
    protected $table = "categories";

    protected $fillable = [
        'title',
        'slug',
        'top_level_category_id',
        'mid_level_category_id',
        'bottom_level_category_id'
    ];

    // Top-level -> mid-level
    public function children()
    {
        return $this->hasMany(Categories::class, 'top_level_category_id', 'id')
                    ->whereNull('mid_level_category_id')
                    ->whereNull('is_delete');
    }

    // Mid-level -> sub-level
    public function subChildren()
    {
        return $this->hasMany(Categories::class, 'mid_level_category_id', 'id')
                    ->whereNull('bottom_level_category_id')
                    ->whereNull('is_delete');
    }

    public function fourthChildren()
    {
        return $this->hasMany(Categories::class, 'bottom_level_category_id', 'id')
                    ->whereNull('is_delete');
    }

    // Optional: breadcrumbs
    public function breadcrumbs()
    {
        $crumbs = [];
        if ($this->mid_level_category_id) {
            $parent = self::find($this->mid_level_category_id);
            if ($parent) $crumbs[] = $parent;
        }
        if ($this->bottom_level_category_id) {
            $parent = self::find($this->bottom_level_category_id);
            if ($parent) $crumbs[] = $parent;
        }
        if ($this->top_level_category_id) {
            $parent = self::find($this->top_level_category_id);
            if ($parent) $crumbs[] = $parent;
        }
        $crumbs[] = $this;
        return $crumbs;
    }
}
