<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HolderChange extends Model
{
    use LogsActivity;

    protected $fillable = [
        'affiliate_id',
        'reason',
        'deceased_at',
        'notes',
        'previous_full_name',
        'previous_id_number',
        'previous_birth_date',
        'previous_phone',
        'previous_email',
        'beneficiary_id',
        'new_full_name',
        'new_id_number',
        'performed_by',
    ];

    protected $casts = [
        'deceased_at'         => 'date',
        'previous_birth_date' => 'date',
    ];

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function activityLabel(): string
    {
        return $this->previous_full_name;
    }
}
