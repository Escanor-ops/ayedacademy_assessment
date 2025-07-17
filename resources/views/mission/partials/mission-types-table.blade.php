@foreach($allMissionTypes as $type)
    <tr>
        <td class="text-center">{{ $loop->iteration }}</td>
        <td>{{ $type->name }}</td>
        <td class="text-center">
            @if($type->status == 0)
                <span class="badge bg-success">نشط</span>
            @else
                <span class="badge bg-danger">غير نشط</span>
            @endif
        </td>
        <td class="text-center">{{ $type->created_at->format('Y-m-d') }}</td>
        @if(auth()->user()->role == 'department_manager' && auth()->user()->department_id == $type->department_id)
            <td class="text-center">
                <button type="button" 
                        class="btn btn-sm w-75 {{ $type->status == 0 ? 'btn-danger' : 'btn-success' }}"
                        onclick="toggleMissionTypeStatus({{ $type->id }}, this)"
                        data-status="{{ $type->status }}">
                    {{ $type->status == 0 ? 'تعطيل' : 'تفعيل' }}
                </button>
            </td>
        @endif
    </tr>
@endforeach 