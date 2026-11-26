<?php
namespace App\Models;
use App\Enums\Purchases\PurchaseStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo,HasMany};
class Purchase extends Model
{
    protected $fillable = ['purchase_no','supplier_id','warehouse_id','user_id','status','subtotal','discount_amount','tax_amount','grand_total','note','ordered_at','received_at'];
    protected function casts(): array { return ['status'=>PurchaseStatus::class,'subtotal'=>'integer','discount_amount'=>'integer','tax_amount'=>'integer','grand_total'=>'integer','ordered_at'=>'datetime','received_at'=>'datetime']; }
    public function supplier(): BelongsTo { return $this->belongsTo(Supplier::class); }
    public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function items(): HasMany { return $this->hasMany(PurchaseItem::class); }
}
