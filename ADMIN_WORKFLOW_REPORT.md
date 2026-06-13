# Admin Workflow Report
**Lombok Nature Culture — Filament BookingResource Review**
*Generated: 2026-06-14*

---

## 1. Current State Assessment

### Booking List (ListBookings)

**What works:**
- Status tabs (All / New / Contacted / Quoted / Confirmed / Completed / Cancelled) with live badge counts
- Navigation badge showing pending-action count (new + contacted) in orange
- Inline row actions for each lifecycle transition — reduces clicks significantly
- Default sort: newest first
- Copyable ref column with monospace font
- Guest name with email sub-description
- Status badge with correct color per status

**Table column inventory:**

| Column | Searchable | Sortable | Notes |
|--------|-----------|---------|-------|
| `ref` | ✓ | — | Monospace, copyable |
| `name` (Guest) | ✓ | ✓ | Email shown as description |
| `package_id` | — | — | Badge format |
| `guests` | — | ✓ | Center-aligned |
| `dates` | — | — | Free-text, truncated to 22 chars |
| `total_amount` | — | ✓ | Formatted IDR |
| `status` | — | — | Badge with lifecycle color |
| `created_at` | — | ✓ | Date format |

### Booking View (ViewBooking)

**What works:**
- 4-column overview: ref, status badge, received date, guide assigned
- 2-column grid: Journey (package, title, duration, guests, dates, flexibility, accommodation) / Guest (name, email, phone, country, nationality, source)
- Pricing section (total, deposit, balance)
- Guest Vision (collapsed): message, special requirements, budget
- Admin Notes + cancellation reason
- Status Timeline via RepeatableEntry
- Invoices list via RepeatableEntry

### Booking Edit (EditBooking)

**Editable sections:**
- Reference (read-only display)
- Operations: Guide assignment, Admin notes
- Pricing: total, deposit, balance
- Journey details: package code, title, duration, guests, dates, accommodation
- Guest Info (collapsed): name, email, phone, country, nationality, source
- Guest Vision (collapsed): message, special, budget

---

## 2. Workflow Issues — Detailed Analysis

### WF-01: State Machine Not Enforced
**Severity: High**

`Booking::canTransitionTo()` is defined in the model but never called by any Filament action. The `visible()` callbacks correctly hide irrelevant actions based on current status, but if two admin tabs are open simultaneously, a race condition could apply the same transition twice. The `transitionTo()` method itself does not check `canTransitionTo()` before executing.

**Fix:** Add guard at top of each action:
```php
->action(function (Booking $record, array $data) {
    if (!$record->canTransitionTo(Booking::STATUS_CONTACTED)) {
        Notification::make()->title('Transition not allowed')->danger()->send();
        return;
    }
    // ... proceed
})
```

---

### WF-02: Assigned Guide Not Visible in Table
**Severity: High**

The bookings table has no `assigned_guide_id` / `assignedGuide.name` column. Staff processing confirmed bookings cannot see at a glance which bookings have guides assigned. The "No Guide Assigned" filter exists but requires active use — staff won't know to check.

**Fix:** Add to table columns:
```php
Tables\Columns\TextColumn::make('assignedGuide.name')
    ->label('Guide')
    ->placeholder('—')
    ->badge()->color('gray')
    ->toggleable(),
```

---

### WF-03: No Date Range Filter
**Severity: High**

Staff cannot filter bookings by:
- When they were received ("all new bookings this week")
- When the travel dates are ("arrivals in August 2026")

This makes daily intake review and pre-journey preparation impossible to do efficiently.

**Fix:**
```php
Tables\Filters\Filter::make('received_this_week')
    ->label('Received This Week')
    ->query(fn ($q) => $q->where('created_at', '>=', now()->startOfWeek())),

Tables\Filters\Filter::make('arriving_this_month')
    ->label('Arriving This Month')
    ->query(fn ($q) => $q->whereMonth('arrival_date', now()->month)),
```
Note: `arrival_date` structured field must be added first (see BOOKING_DATA_GAP_ANALYSIS.md GAP-01).

---

### WF-04: Bulk Actions Limited to Delete Only
**Severity: High**

The only bulk action available is `DeleteBulkAction`. For a team processing 10–20 new bookings per week, the inability to bulk-mark as contacted or bulk-export is a major bottleneck.

**Missing bulk actions:**
1. "Mark as Contacted" — for batch intake processing when team sends intro email blast
2. "Export to CSV" — for guide briefings and finance reporting
3. "Assign Guide" — when allocating guides to a batch of confirmed bookings

**Fix:**
```php
->bulkActions([
    Tables\Actions\BulkActionGroup::make([
        Tables\Actions\BulkAction::make('mark_contacted')
            ->label('Mark as Contacted')
            ->icon('heroicon-o-phone')
            ->requiresConfirmation()
            ->action(fn ($records) => $records->each(
                fn ($r) => $r->status === Booking::STATUS_NEW
                    ? $r->transitionTo(Booking::STATUS_CONTACTED, Auth::user()?->email, 'Bulk contact action')
                    : null
            )),
        Tables\Actions\ExportBulkAction::make(),
    ]),
])
```

---

### WF-05: No CSV / Excel Export
**Severity: High**

Guides need printable briefing sheets. Finance needs monthly booking summaries. Neither is possible without export functionality.

**Required export columns:**
- Ref, Status, Guest Name, Email, Phone, Package, Arrival Date, Departure Date, Guests, Total, Guide, Accommodation, Dietary Requirements, Emergency Contact

---

### WF-06: Package Title vs Code in Table
**Severity: Medium**

The table shows `package_id` (code: "LNC-3D") as a badge. This provides minimal context. The code is meaningful to staff, but seeing "LNC-3D" requires memorizing the package catalog. Showing the title as a tooltip or sub-description would help.

**Fix:**
```php
Tables\Columns\TextColumn::make('package_id')
    ->label('Package')
    ->badge()->color('info')
    ->description(fn (Booking $r) => $r->package_title),
```

---

### WF-07: No Invoice Due Date on Booking View
**Severity: Medium**

When viewing a confirmed booking, the Invoices section shows: invoice number, type, status, total, issued date, valid until. It does not show `due_deposit_at` — the date by which the deposit was due. For confirmed bookings, this is the most operationally relevant date.

**Fix:** Add `due_deposit_at` to the invoice RepeatableEntry in the infolist.

---

### WF-08: Cannot Reassign Guide from List
**Severity: Medium**

Reassigning a guide requires opening the Edit page. For bulk guide assignment (e.g. allocating 5 bookings to one guide), this means 5 separate edit page loads.

**Fix:** Add inline "Assign Guide" action to table:
```php
Tables\Actions\Action::make('assign_guide')
    ->label('Guide')
    ->icon('heroicon-o-user-plus')
    ->form([
        Forms\Components\Select::make('assigned_guide_id')
            ->options(fn () => TeamMember::where('is_active', true)->pluck('name', 'id'))
            ->required(),
    ])
    ->action(fn (Booking $record, array $data) =>
        $record->update(['assigned_guide_id' => $data['assigned_guide_id']])
    ),
```

---

### WF-09: No Re-quote Path
**Severity: Medium**

When a guest receives a quote and wants to negotiate (different number of guests, different accommodation tier, different dates), there is no "back to contacted" path. Admin must either:
- Manually edit pricing fields (no audit trail for price change)
- Cancel the booking and create a new one (loses history)

**Recommended:** Add `Send Revised Quote` action on `quoted` status that allows updating pricing and creating a new invoice version, returning status to `contacted` with a log entry.

---

### WF-10: Source Filter Missing from Bookings
**Severity: Low-Medium**

CustomerResource has country and source filters. BookingResource has none of these — only status, "has price", and "no guide". Marketing team cannot filter "show me all Instagram bookings from Q2" in the bookings view.

---

### WF-11: No Urgency Indicator
**Severity: Low**

All new bookings look identical in the list. A booking submitted 5 minutes ago and one from 3 weeks ago that was never actioned look the same. Staff have no visual cue for which new bookings are most overdue for contact.

**Recommendation:** Color-code the `created_at` cell: green (< 24h), yellow (24–48h), orange (48–72h), red (> 72h without contact). Or add a `days_waiting` calculated column.

---

## 3. Recommended Priority Changes

### P1 — Critical (fix before go-live)

| Change | Effort |
|--------|--------|
| Enforce state machine in actions (`canTransitionTo` check) | 30 min |
| Add assigned guide column to table | 15 min |
| Add date range filter (received this week / arriving this month) | 30 min |
| Require guide assignment before confirming | 20 min |

### P2 — High (within first week of live)

| Change | Effort |
|--------|--------|
| Add "Mark as Contacted" bulk action | 30 min |
| Add CSV export | 1 hour |
| Add package title as sub-description in table | 10 min |
| Add deposit due date to invoice RepeatableEntry | 15 min |

### P3 — Medium (within first month)

| Change | Effort |
|--------|--------|
| Inline guide assign action | 30 min |
| Source / country filter on bookings | 20 min |
| Re-quote path on quoted bookings | 1 hour |
| Urgency color on created_at | 20 min |

---

## 4. Current Workflow — Step by Step

Here is how a staff member must process bookings today, and what the friction points are:

### Intake (morning routine)

1. Open `/admin/bookings` → click "New" tab
2. For each booking:
   - Click row action "Contact" → fill notes → confirm (**1 click per booking — good**)
3. Go to email client (separate tool — **not integrated**)
4. Manually send intro email or WhatsApp (**no admin integration — gap**)

### Quote preparation

1. Receive guest response (via WhatsApp or email — **outside the system**)
2. Open booking → click "Send Quote" → enter total amount
3. System creates invoice and updates status (**good**)
4. Must manually email invoice or pricing to guest (**not integrated — gap**)

### Confirmation

1. Receive guest acceptance (outside system)
2. Open booking → click "Confirm Booking" → add notes
3. **Problem: no prompt to assign guide** at this step
4. Must separately open Edit → assign guide → save

### Pre-departure

1. No "arriving today" or "arriving this week" view (**critical gap**)
2. No way to pull briefing sheet data (**export missing**)
3. Guide needs to be contacted separately (**not integrated**)
