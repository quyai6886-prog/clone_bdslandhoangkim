<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Property;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $query = Property::query()->select(Property::COLUMNS)->visibleTo($request->user())
            ->with(['project', 'firstImage', 'latestSaleNote', 'latestRentNote', 'customers'])
            ->withExists(['media as has_video' => fn (Builder $media) => $media->where('media_type', 'video')])
            ->orderByDesc('id');

        $query->when($request->filled('q'), function (Builder $q) use ($request) {
            $term = '%'.$request->string('q')->trim().'%';
            $q->where(function (Builder $search) use ($term) {
                $search->where('code', 'like', $term)
                    ->orWhere('property_type', 'like', $term)
                    ->orWhereHas('project', fn (Builder $p) => $p->where('project_name', 'like', $term))
                    ->orWhereHas('customers', fn (Builder $c) => $c->where('full_name', 'like', $term)->orWhere('phone1', 'like', $term));
            });
        });

        foreach (['project_id', 'tower', 'floor', 'room', 'property_type', 'interior', 'status_new'] as $field) {
            $query->when($request->filled($field), fn (Builder $q) => $q->where($field, $request->input($field)));
        }
        foreach ([['price_sell', 'price_sell_min', '>='], ['price_sell', 'price_sell_max', '<='], ['price_rent', 'price_rent_min', '>='],
            ['price_rent', 'price_rent_max', '<='], ['area', 'area_min', '>='], ['area', 'area_max', '<='],
            ['sales_commission', 'commission_min', '>='], ['sales_commission', 'commission_max', '<=']] as [$column, $input, $operator]) {
            $query->when($request->filled($input), fn (Builder $q) => $q->where($column, $operator, $request->input($input)));
        }
        $query->when($request->boolean('has_image'), fn (Builder $q) => $q->whereHas('media', fn (Builder $m) => $m->where('media_type', 'image')));
        $query->when($request->boolean('has_document'), fn (Builder $q) => $q->whereHas('media', fn (Builder $m) => $m->where('media_type', 'document')));

        $perPage = in_array((int) $request->input('per_page'), [10, 20, 50, 100], true) ? (int) $request->input('per_page') : 20;
        $properties = $query->paginate($perPage)->withQueryString();
        $visibleBase = Property::query()->visibleTo($request->user());
        $projects = Project::query()->whereHas('properties', fn (Builder $q) => $q->visibleTo($request->user()))->orderBy('project_name')->get();
        $options = [];
        foreach (['tower', 'floor', 'room', 'property_type', 'interior', 'status_new'] as $field) {
            $options[$field] = (clone $visibleBase)->whereNotNull($field)->where($field, '<>', '')->distinct()->orderBy($field)->pluck($field);
        }

        return view('dashboard.index', compact('properties', 'projects', 'options'));
    }
}
