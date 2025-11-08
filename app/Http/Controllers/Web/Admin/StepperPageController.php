<?php

namespace App\Http\Controllers\Web\Admin;


use App\Facades\DataTable;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\StepperPage;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class StepperPageController extends Controller
{
    public function index(Request $request): Response
    {
        $perPage = $request->per_page ?? 10;
        $search = $request->search;

        $query = StepperPage::query();
        if ($search) {
            $query->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        }

        $pages = $query->orderBy('order_no')->paginate($perPage);
        // return Inertia::render('dashboard', [
        //     'pages' => $pages,
        //     'filters' => [
        //         'search' => $search,
        //         'per_page' => $perPage,
        //     ],
        // ]);
        // dd($pages->toArray());
        return Inertia::render('admin/stepper-pages/index', [
            'pages' => $pages,
            'filters' => [
                'search' => $search,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/stepper-pages/create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'order_no' => 'required|integer|min:0',
            'status' => 'required|string|in:active,inactive',
        ]);

        StepperPage::create($validated);

        return redirect()->route('admin.stepper-pages.index')->with('success', 'Stepper page created successfully.');
    }

    public function show(string $id): Response
    {
        $page = StepperPage::findOrFail($id);

        return Inertia::render('admin/stepper-pages/show', [
            'page' => $page,
        ]);
    }

    public function edit(string $id): Response
    {
        $page = StepperPage::findOrFail($id);

        return Inertia::render('admin/stepper-pages/edit', [
            'page' => $page,
        ]);
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'order_no' => 'required|integer|min:0',
            'status' => 'required|string|in:active,inactive',
        ]);

        $page = StepperPage::findOrFail($id);
        $page->update($validated);

        return redirect()->route('admin.stepper-pages.index')->with('success', 'Stepper page updated successfully.');
    }

    public function destroy(string $id): RedirectResponse
    {
        $page = StepperPage::findOrFail($id);
        $page->delete();

        return redirect()->route('admin.stepper-pages.index')->with('success', 'Stepper page deleted successfully.');
    }

    public function toggleStatus(string $id)
    {
        $page = StepperPage::findOrFail($id);
        $page->status = $page->status === 'active' ? 'inactive' : 'active';
        $page->save();
        return back()->with('success', 'Status updated.');
    }

    public function newIndex(Request $request): Response
    {
        $sort = str_replace(
            ['name'],
            ['name'],
            request()->query('col')
        );

        $result = DataTable::query(StepperPage::query())
            ->searchable(['name'])
            ->applySort($sort)
            ->allowedSorts(['name'])
            ->make();


        return Inertia::render('admin/stepper-pages/partials/index', [
            'pages' => $result,
            // 'filters' => [
            //     'search' => $search,
            //     'per_page' => $perPage,
            // ],
        ]);
    }
}
