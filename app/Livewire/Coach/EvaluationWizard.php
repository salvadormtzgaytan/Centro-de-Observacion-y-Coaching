<?php

namespace App\Livewire\Coach;

use App\Models\Division;
use App\Models\EvaluationSession;
use App\Models\GuideGroup;
use App\Models\GuideResponse;
use App\Models\GuideTemplate;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;
use Livewire\WithPagination;
use RealRashid\SweetAlert\Facades\Alert;

class EvaluationWizard extends Component
{
    use WithPagination;

    public int $currentStep = 1;

    public ?int $coacheeId = null;

    public ?User $coachee = null;

    public ?int $cycleId = null;

    public ?string $useGroup = null;      // '1' = grupo, '0' = personalizada

    public ?int $guideGroupId = null;     // paso 3 si useGroup = '1'

    public array $guideTemplateIds = [];  // paso 3 si useGroup = '0'

    /** División elegida en el paso 3 (obligatoria en todos los casos) */
    public ?int $divisionId = null;

    public Collection $answers;

    // UX / validación
    public bool $showTemplateSelectionError = false;

    // Evitar doble submit
    public bool $saving = false;

    public function mount(): void
    {
        $this->answers = collect();
    }

    public function updatedUseGroup(): void
    {
        // Cambian el método, limpiamos selección y división para evitar estados colgantes
        $this->guideGroupId = null;
        $this->guideTemplateIds = [];
        $this->divisionId = null;
        $this->showTemplateSelectionError = false;
        $this->cycleId = null;
    }

    /** Si cambian la división, limpiamos la selección de guías personalizadas */
    public function updatedDivisionId(): void
    {
        $this->guideTemplateIds = [];
        $this->showTemplateSelectionError = false;
    }

    public function updatedGuideTemplateIds(): void
    {
        if (! empty($this->guideTemplateIds)) {
            $this->showTemplateSelectionError = false;
        }
    }

    public function nextStep(): void
    {
        try {
            match ($this->currentStep) {
                1 => $this->validateCoachee(),
                2 => $this->validateGuideMethod(),
                3 => $this->validateTemplateSelection(),
                default => null,
            };

            $this->showTemplateSelectionError = false;
            $this->currentStep++;
        } catch (ValidationException $e) {
            if ($this->currentStep === 3 && $this->useGroup === '0' && empty($this->guideTemplateIds)) {
                $this->showTemplateSelectionError = true;
            }
            throw $e;
        }
    }

    public function previousStep(): void
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    protected function validateCoachee(): void
    {
        if (! $this->coacheeId) {
            throw ValidationException::withMessages([
                'coacheeId' => 'Selecciona un colaborador para continuar.',
            ]);
        }

        $descendants = Auth::user()->descendants()->pluck('id')->toArray();
        if (! in_array($this->coacheeId, $descendants, true)) {
            throw ValidationException::withMessages([
                'coacheeId' => 'Solo puedes evaluar colaboradores bajo tu estructura.',
            ]);
        }

        $this->coachee = User::findOrFail($this->coacheeId);
    }

    protected function validateGuideMethod(): void
    {
        if (! in_array($this->useGroup, ['0', '1'], true)) {
            throw ValidationException::withMessages([
                'useGroup' => 'Debes seleccionar un método para continuar.',
            ]);
        }
    }

    protected function validateTemplateSelection(): void
    {
        // División obligatoria SIEMPRE en el paso 3
        if (! $this->divisionId) {
            throw ValidationException::withMessages([
                'divisionId' => 'Selecciona una división.',
            ]);
        }
        if (! $this->cycleId) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'cycleId' => 'Selecciona un ciclo.',
            ]);
        }

        if (! $this->cycleId) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'cycleId' => 'El ciclo seleccionado no es válido.',
            ]);
        }

        if ($this->useGroup === '1') {
            if (! $this->guideGroupId) {
                throw ValidationException::withMessages([
                    'guideGroupId' => 'Selecciona un grupo de guías válido.',
                ]);
            }

            $group = GuideGroup::with('templates')->find($this->guideGroupId);
            if (! $group || $group->templates->isEmpty()) {
                throw ValidationException::withMessages([
                    'guideGroupId' => 'El grupo seleccionado no contiene guías.',
                ]);
            }
        } else {
            if (empty($this->guideTemplateIds)) {
                throw ValidationException::withMessages([
                    'guideTemplateIds' => 'Selecciona al menos una guía del catálogo.',
                ]);
            }
        }
    }

    /**
     * Guarda la evaluación y evita doble submit.
     */
    public function save(): RedirectResponse|Redirector
    {
        if ($this->saving) {
            return redirect()->route('evaluation.index');
        }
        $this->saving = true;

        $this->validateTemplateSelection();

        DB::transaction(function (): void {
            /** @var \App\Models\Cycle $cycle */
            $cycle = \App\Models\Cycle::findOrFail($this->cycleId);
            $cycleLabel = "FY{$cycle->fiscal_year}-{$cycle->code}";
            $session = EvaluationSession::create([
                'evaluator_id' => Auth::id(),
                'participant_id' => $this->coacheeId,
                'date' => now()->toDateString(),
                'status' => 'draft',
                'division_id' => $this->divisionId,
                'cycle' => $cycleLabel,
            ]);

            if ($this->useGroup === '1') {
                $group = GuideGroup::with('templates')->find($this->guideGroupId);
                foreach ($group->templates as $template) {
                    GuideResponse::firstOrCreate([
                        'session_id' => $session->id,
                        'guide_template_id' => $template->id,
                    ]);
                }
            } else {
                foreach ($this->guideTemplateIds as $templateId) {
                    GuideResponse::firstOrCreate([
                        'session_id' => $session->id,
                        'guide_template_id' => $templateId,
                    ]);
                }
            }
        });

        Alert::toast('Evaluación creada correctamente.', 'success')
            ->persistent(false)
            ->autoClose(4000);

        return redirect()->route('evaluation.index');
    }

    public function render(): View
    {
        $defaultFy = (int) config('coaching.default_fiscal_year');

        return view('livewire.coach.evaluation-wizard', [
            'coacheesPaginated' => Auth::user()->descendants()
                ->role('coachee')
                ->orderBy('name')
                ->paginate(10),

            'guideGroups' => GuideGroup::with('templates')->get(),

            // Lista de divisiones disponibles (sin impacto en el catálogo de guías)
            'divisions' => Division::all(['id', 'name']),

            // Catálogo de guías publicadas (NO depende de la división)
            'catalogTemplates' => GuideTemplate::query()
                ->where('status', 'published')
                ->orderBy('name')
                ->get(),
            // Ciclos activos, priorizando el año fiscal por defecto
            'cycles' => \App\Models\Cycle::query()
                // ->where('is_open', true)
                ->orderByRaw('fiscal_year = ? desc', [$defaultFy]) // primero el FY por defecto
                ->orderBy('fiscal_year')
                ->orderByRaw("FIELD(code,'Q1','Q2','Q3','Q4')") // orden lógico
                ->get(['id', 'code', 'label', 'fiscal_year']),
        ]);
    }
}
