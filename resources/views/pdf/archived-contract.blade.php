<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Archived Contract #{{ $archive->original_record_id }}</title>
<style>
    body { font-family: DejaVu Sans, sans-serif; color: #1f2937; font-size: 11px; line-height: 1.5; }
    h1 { font-size: 18px; margin: 0 0 4px; color: #111827; }
    .eyebrow { font-size: 9px; letter-spacing: 0.18em; text-transform: uppercase; color: #6b7280; }
    .meta { margin: 14px 0 22px; color: #4b5563; font-size: 10px; }
    .meta strong { color: #111827; }
    table.kv { width: 100%; border-collapse: collapse; margin: 6px 0 18px; }
    table.kv th, table.kv td { text-align: left; padding: 6px 8px; border: 1px solid #e5e7eb; vertical-align: top; }
    table.kv th { width: 32%; background: #f9fafb; color: #374151; font-weight: 600; }
    h2 { font-size: 13px; margin: 18px 0 6px; color: #111827; border-bottom: 1px solid #d1d5db; padding-bottom: 3px; }
    .scan-note { background: #fef3c7; border: 1px solid #fcd34d; padding: 8px 10px; border-radius: 3px; font-size: 10px; }
    .footer { margin-top: 24px; font-size: 9px; color: #6b7280; border-top: 1px solid #e5e7eb; padding-top: 8px; }
    .empty { color: #9ca3af; font-style: italic; }
</style>
</head>
<body>

<div class="eyebrow">Citiescapes Apartment Rental — SS5 Archive</div>
<h1>Archived Contract #{{ $archive->original_record_id }}</h1>

<div class="meta">
    <strong>Archive ID:</strong> {{ $archive->id }} &nbsp;·&nbsp;
    <strong>Source:</strong> {{ $archive->source_subsystem }} &nbsp;·&nbsp;
    <strong>Archived:</strong> {{ $archive->created_at?->format('M d, Y H:i') ?? '—' }}<br>
    <strong>Archived By:</strong> {{ optional($archive->archivedByUser)->full_name ?? 'system (auto-archive)' }}<br>
    <strong>Reason:</strong> {{ $archive->archive_reason ?: '—' }}
</div>

<h2>Contract Details</h2>
<table class="kv">
    @php
        $fields = [
            'Tenant ID'           => $data['tenant_id'] ?? null,
            'Room ID'             => $data['room_id'] ?? null,
            'Status (at archive)' => $data['status'] ?? null,
            'Start Date'          => $data['start_date'] ?? null,
            'End Date'            => $data['end_date'] ?? null,
            'Activated At'        => $data['activated_at'] ?? null,
            'Terminated At'       => $data['terminated_at'] ?? null,
            'Termination Reason'  => $data['termination_reason'] ?? null,
            'Base Rent Rate'      => isset($data['base_rent_rate']) ? '₱' . number_format((float) $data['base_rent_rate'], 2) : null,
            'Deposit'             => isset($data['deposit']) ? '₱' . number_format((float) $data['deposit'], 2) : null,
            'Room Key Fee'        => isset($data['room_key_fee']) ? '₱' . number_format((float) $data['room_key_fee'], 2) : null,
            'Penalty Rate'        => isset($data['penalty_rate']) ? '₱' . number_format((float) $data['penalty_rate'], 2) . ' / day' : null,
            'Penalty Grace Days'  => $data['penalty_grace_days'] ?? null,
            'Step 1 Acknowledged' => $data['step1_acknowledged_at'] ?? null,
            'Step 2 Acknowledged' => $data['step2_acknowledged_at'] ?? null,
        ];
    @endphp
    @foreach($fields as $label => $val)
        <tr>
            <th>{{ $label }}</th>
            <td>{!! $val !== null && $val !== '' ? e($val) : '<span class="empty">—</span>' !!}</td>
        </tr>
    @endforeach
</table>

@if(!empty($data['requested_amenities']) && is_array($data['requested_amenities']))
<h2>Requested Amenities</h2>
<table class="kv">
    <thead>
        <tr><th style="width:60%">Name</th><th>Fee</th></tr>
    </thead>
    <tbody>
    @foreach($data['requested_amenities'] as $amenity)
        <tr>
            <td>{{ $amenity['name'] ?? '—' }}</td>
            <td>₱{{ number_format((float) ($amenity['fee'] ?? 0), 2) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
@endif

@if(!empty($data['house_rules']))
<h2>House Rules</h2>
<div style="white-space: pre-wrap; padding: 6px 0;">{{ $data['house_rules'] }}</div>
@endif

<h2>Signed Scan</h2>
@if($scanUrl)
    <div class="scan-note">
        Original scanned contract is preserved at:<br>
        <strong>{{ $scanUrl }}</strong><br>
        File path on disk: <code>{{ $archive->scan_file_path }}</code>
    </div>
@else
    <div class="empty">No scan was on file at the time of archival.</div>
@endif

<div class="footer">
    Generated {{ now()->format('M d, Y H:i') }} by Citiescapes Apartment Rental Management System ·
    Archive #{{ $archive->id }} · SS5 Report &amp; Archive Management
</div>

</body>
</html>
