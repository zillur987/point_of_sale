<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class SaleItem extends Model {
 public $timestamps=false;
 protected $fillable=['sale_id','product_variant_id','product_name','variant_name','sku','quantity','unit_price','discount_amount','tax_amount','line_total'];
 protected function casts(): array { return ['quantity'=>'decimal:3','unit_price'=>'integer','discount_amount'=>'integer','tax_amount'=>'integer','line_total'=>'integer']; }
 public function variant(): BelongsTo { return $this->belongsTo(ProductVariant::class,'product_variant_id'); }
}
