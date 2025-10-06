@extends('admin.layouts.grid')
@section('section', admin_lang('Settings'))
@section('title', admin_lang('Engines'))
@section('content')
    <div class="card">
        <table class="table ask-datatable w-100">
            <thead>
                <tr>
                    <th class="tb-w-1x">{{ admin_lang('#') }}</th>
                    <th class="tb-w-3x">{{ admin_lang('Logo') }}</th>
                    <th class="tb-w-3x">{{ admin_lang('name') }}</th>
                    <th class="tb-w-3x">{{ admin_lang('API Max Images') }}</th>
                    <th class="tb-w-7x">{{ admin_lang('Status') }}</th>
                    <th class="tb-w-7x">{{ admin_lang('Last Update') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($engines as $engine)
                    <tr class="item">
                        <td>{{ $engine->id }}</td>
                        <td>
                            <a href="{{ route('admin.settings.engines.edit', $engine->id) }}">
                                <img src="{{ asset($engine->logo) }}" height="40px" width="40px">
                            </a>
                        </td>
                        <td>
                            <a href="{{ route('admin.settings.engines.edit', $engine->id) }}" class="text-dark">
                                {{ $engine->name }}
                            </a>
                        </td>
                        <td>{{ $engine->max }}</td>
                        <td>
                            @if ($engine->isActive())
                                <span class="badge bg-success">{{ admin_lang('Active') }}</span>
                            @else
                                <span class="badge bg-danger">{{ admin_lang('Disabled') }}</span>
                            @endif
                        </td>
                        <td>{{ dateFormat($engine->updated_at) }}</td>
                        <td>
                            <div class="text-end">
                                <button type="button" class="btn btn-sm rounded-3" data-bs-toggle="dropdown"
                                    aria-expanded="true">
                                    <i class="fa fa-ellipsis-v fa-sm text-muted"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-sm-end" data-popper-placement="bottom-end">
                                    <li>
                                        <a class="dropdown-item"
                                            href="{{ route('admin.settings.engines.edit', $engine->id) }}"><i
                                                class="fa fa-edit me-2"></i>{{ admin_lang('Edit') }}</a>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
