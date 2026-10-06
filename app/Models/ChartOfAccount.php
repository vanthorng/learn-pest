<?php

namespace App\Models;

use Database\Factories\ChartOfAccountFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['code', 'name', 'type'])]
class ChartOfAccount extends Model
{
    /** @use HasFactory<ChartOfAccountFactory> */
    use HasFactory;

    /**
     * Get the team that owns this account.
     *
     * @return BelongsTo<Team, $this>
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
