<?php

namespace App\Http\Controllers\Implementations;

use App\Constrainters\Constrainter;
use App\Constrainters\Implementations\IdentifierConstrainter;
use App\Http\Controllers\DynamicController;
use App\Http\Requests\SpaceStoreRequest;
use App\Http\Requests\SpaceUpdateRequest;
use App\Models\Orders\SpaceOrder;
use App\Models\Space;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Validator\Constraints as Assert;

class SpaceController extends DynamicController
{
    /**
     * Controller's model class name.
     *
     * @var string
     */
    protected ?string $model = Space::class;

    /**
     * Controller's store method form request class name. Must extend DataFieldRequest.
     *
     * @var ?string
     */
    protected ?string $storeFormRequest = SpaceStoreRequest::class;

    /**
     * Controller's update method form request class name. Must extend DataFieldRequest.
     *
     * @var ?string
     */
    protected ?string $updateFormRequest = SpaceUpdateRequest::class;

    /**
     * Find instance of model by it's primary keys.
     *
     * @param mixed|array|null $id
     * @param string|null $dataKey
     * @param string|null $trashed
     * @return null
     */
    public function findModel($id = null, $dataKey = null, $trashed = null)
    {
        $instance = parent::findModel($id, $dataKey, $trashed);

        if (isset($instance)) {
            $begDatetime = $this->extractDatetime(request()->all(), 'beg_datetime');
            $endDatetime = $this->extractDatetime(request()->all(), 'end_datetime');

            // setting beginning to current datetime if only ending was specified
            if (empty($begDatetime) && !empty($endDatetime)) {
                $begDatetime = Carbon::now()->toDateTimeString();
            }

            $banquetIdConditions = $this->whereConditions(
                ['banquet_id' => request()->get('banquet_id') ?? []],
                true,
                false
            );

            $instance->intervals = $this->loadIntervals($instance, $begDatetime, $endDatetime)
                ->filter(function ($interval) use ($banquetIdConditions) {
                    return $this->isMatchingWhereConditions($interval, $banquetIdConditions);
                });

            // filling array of performed filters
            if (isset($begDatetime) && isset($endDatetime)) {
                $this->currentFilters[] = [
                    ['beg_datetime', 'between', [$begDatetime, $endDatetime]],
                    'or',
                    ['end_datetime', 'between', [$begDatetime, $endDatetime]]
                ];
            } else {
                $this->currentFilters[] = ['beg_datetime', '>=', $begDatetime];
            }

            $this->currentFilters = array_merge(
                $this->currentFilters,
                $banquetIdConditions
            );
        }

        return $instance;
    }

    /**
     * Get filtered and sorted collection of the model instances with intervals if beg and/or end datetime are specified.
     *
     * @param array|null $filters where conditions [[key, comparison, value]]
     * @param array|null $sorts orderBy conditions [key, order]
     * @param string|null $trashed
     * @return \Illuminate\Support\Collection
     * @throws \Illuminate\Validation\ValidationException
     */
    public function allModels($filters = null, $sorts = null, $trashed = null)
    {
        $collection = parent::allModels($filters, $sorts, $trashed);
        if ($collection->count() === 0) {
            return $collection;
        }

        $begDatetime = $this->extractDatetime(request()->all(), 'beg_datetime');
        $endDatetime = $this->extractDatetime(request()->all(), 'end_datetime');

        // setting beginning to current datetime if only ending was specified
        if (empty($begDatetime) && !empty($endDatetime)) {
            $begDatetime = Carbon::now()->toDateTimeString();
        }

        $banquetIdConditions = $this->whereConditions(
            ['banquet_id' => request()->get('banquet_id') ?? []],
            true,
            false
        );


        foreach ($collection as $item) {
            $item->intervals = $this->loadIntervals($item, $begDatetime, $endDatetime)
                ->filter(function ($interval) use ($banquetIdConditions) {
                    return $this->isMatchingWhereConditions($interval, $banquetIdConditions);
                });
        }

        // filling array of performed filters
        if (isset($begDatetime) && isset($endDatetime)) {
            $this->currentFilters[] = [
                ['beg_datetime', 'between', [$begDatetime, $endDatetime]],
                'or',
                ['end_datetime', 'between', [$begDatetime, $endDatetime]]
            ];
        } else {
            $this->currentFilters[] = ['beg_datetime', '>=', $begDatetime];
        }

        $this->currentFilters = array_merge(
            $this->currentFilters,
            $banquetIdConditions
        );

        return $collection;
    }

    /**
     * Get validated datetime from data.
     *
     * @param array $data
     * @param string $datetimeKey
     * @return mixed
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function extractDatetime($data, $datetimeKey)
    {
        $queries = $this->validateRules($data, [
            $datetimeKey => Constrainter::getRules(false, [new Assert\DateTime()]),
        ]);
        return $queries[$datetimeKey] ?? null;
    }

    /**
     * Loads Space's business intervals for the specified period of time.
     *
     * @param Space $item
     * @param mixed $begDatetime
     * @param mixed $endDatetime
     * @param array $additionalWhereConditions
     * @return \Illuminate\Database\Eloquent\Collection|Collection
     */
    protected function loadIntervals($item, $begDatetime, $endDatetime)
    {
        if (empty($begDatetime) && empty($endDatetime)) {
            return new Collection();
        }

        if ($item instanceof Space) {
            $builder = $item->intervals();
            if (isset($begDatetime) && isset($endDatetime)) {
                // captures all intervals that intersect the specified interval
                $builder->where(function ($builder) use ($begDatetime, $endDatetime) {
                    $builder->where(function ($builder) use ($begDatetime, $endDatetime) {
                        // starts between/on beginning and end
                        $builder->where('beg_datetime', '>=', $begDatetime) // starts after/on beginning
                        ->where('beg_datetime', '<=', $endDatetime); // starts before/on ending
                    })->orWhere(function ($builder) use ($begDatetime, $endDatetime) {
                        // ends between/on beginning and end
                        $builder->where('end_datetime', '>=', $begDatetime) // ends after/on beginning
                        ->where('end_datetime', '<=', $endDatetime); // ends before/on ending
                    })->orWhere(function ($builder) use ($begDatetime, $endDatetime) {
                        // starts before/on beginning and ends after/on ending
                        $builder->where('beg_datetime', '<=', $begDatetime) // starts before/on beginning
                        ->where('end_datetime', '>=', $endDatetime); // ends after/on ending
                    });
                });
            } else if (isset($begDatetime)) {
                // captures all intervals starting from specified datetime
                $builder->where(function ($builder) use ($begDatetime) {
                    $builder->where(function ($builder) use ($begDatetime) {
                        $builder->where('end_datetime', '>=', $begDatetime) // ends after/on beginning
                        ->where('beg_datetime', '<=', $begDatetime); // begins before/on beginning
                    })->orWhere('beg_datetime', '>=', $begDatetime); // begins after/on beginning
                });
            }

            $intervals = $builder->get();
            foreach ($intervals as $interval) {
                $interval->makeHidden('banquet');
                $interval->banquet_id = $interval->banquet->banquet_id ?? null;
            }

            return $intervals;
        }

        return new Collection();
    }

}
