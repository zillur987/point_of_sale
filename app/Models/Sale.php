<?php
namespace App\Models;
use App\Enums\Sales\PaymentMethod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo,HasMany};
class Sale extends Model {
 protected $fillable=['invoice_no','warehouse_id','customer_id','user_id','status','subtotal','discount_amount','tax_amount','grand_total','paid_amount','change_amount','note','request_key','sold_at'];
 protected function casts(): array { return ['sold_at'=>'datetime','subtotal'=>'integer','discount_amount'=>'integer','tax_amount'=>'integer','grand_total'=>'integer','paid_amount'=>'integer','change_amount'=>'integer']; }
 public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }
 public function user(): BelongsTo { return $this->belongsTo(User::class); }
 public function items(): HasMany { return $this->hasMany(SaleItem::class); }
 public function payments(): HasMany { return $this->hasMany(SalePayment::class); }
}
