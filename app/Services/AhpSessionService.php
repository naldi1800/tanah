<?php

namespace App\Services;

use Illuminate\Support\Facades\Session;
use App\Services\AhpRecommendationService;
use App\Services\AhpService;

/**
 * Service untuk mengelola session pairwise matrix
 * Menyimpan perubahan pairwise ke session selama 30 menit
 */
class AhpSessionService
{
    protected AhpConfigService $configService;

    protected const SESSION_TIMEOUT = 1800; // 30 menit dalam detik
    protected const SESSION_KEY_CRITERIA = 'ahp_pairwise_criteria';
    protected const SESSION_KEY_ALTERNATIVES = 'ahp_pairwise_alternatives';
    protected const SESSION_KEY_TIMESTAMP = 'ahp_session_timestamp';
    protected const SESSION_KEY_DATA_HASH = 'ahp_data_hash'; // Untuk mendeteksi perubahan data

    public function __construct(?AhpConfigService $configService = null)
    {
        $this->configService = $configService ?? new AhpConfigService();
    }

    /**
     * Initialize session jika belum ada
     */
    protected function ensureSessionExists(): void
    {
        if (!Session::has(self::SESSION_KEY_TIMESTAMP)) {
            $this->resetCriteriaMatrix();
            $this->resetAlternativesMatrix();
            Session::put(self::SESSION_KEY_TIMESTAMP, now()->timestamp);
            $this->updateDataHash();
        }
    }

    /**
     * Update hash data untuk mendeteksi perubahan
     */
    protected function updateDataHash(): void
    {
        $alternatives = $this->configService->getAlternativesWithAggregatedData();
        $hash = md5(json_encode($alternatives));
        Session::put(self::SESSION_KEY_DATA_HASH, $hash);
    }

    /**
     * Check apakah data tanah berubah
     */
    protected function hasDataChanged(): bool
    {
        $currentAlternatives = $this->configService->getAlternativesWithAggregatedData();
        $currentHash = md5(json_encode($currentAlternatives));
        $storedHash = Session::get(self::SESSION_KEY_DATA_HASH);

        return $currentHash !== $storedHash;
    }

    /**
     * Check apakah session sudah expired
     */
    public function isSessionExpired(): bool
    {
        if (!Session::has(self::SESSION_KEY_TIMESTAMP)) {
            return true;
        }

        $timestamp = Session::get(self::SESSION_KEY_TIMESTAMP);
        $elapsed = now()->timestamp - $timestamp;

        return $elapsed > self::SESSION_TIMEOUT;
    }

    /**
     * Extend session timeout
     */
    public function extendSession(): void
    {
        Session::put(self::SESSION_KEY_TIMESTAMP, now()->timestamp);
    }

    /**
     * Get pairwise matrix criteria dari session
     */
    public function getCriteriaMatrix(): array
    {
        $this->ensureSessionExists();

        if ($this->isSessionExpired() || $this->hasDataChanged()) {
            $this->resetCriteriaMatrix();
            $this->resetAlternativesMatrix();
            $this->updateDataHash();
        }

        return Session::get(self::SESSION_KEY_CRITERIA, []);
    }

    /**
     * Set pairwise matrix criteria ke session
     */
    public function setCriteriaMatrix(array $matrix): void
    {
        $this->extendSession();
        Session::put(self::SESSION_KEY_CRITERIA, $matrix);
    }

    /**
     * Update nilai tertentu pada pairwise criteria matrix
     * 
     * @param int $row Index baris
     * @param int $col Index kolom
     * @param float $value Nilai baru
     */
    public function updateCriteriaValue(int $row, int $col, float $value): void
    {
        $matrix = $this->getCriteriaMatrix();
        $matrix[$row][$col] = $value;
        // Update reciprocal
        $matrix[$col][$row] = $value !== 0 ? 1 / $value : 0;
        $this->setCriteriaMatrix($matrix);
    }

    /**
     * Reset pairwise criteria ke default
     */
    public function resetCriteriaMatrix(): void
    {
        $defaultMatrix = $this->configService->getDefaultPairwiseMatrix();
        $this->setCriteriaMatrix($defaultMatrix);
    }

    /**
     * Get pairwise matrix alternatif dari session
     * Format: ['criterion_name' => matrix]
     */
    public function getAlternativesMatrix(): array
    {
        $this->ensureSessionExists();

        if ($this->isSessionExpired()) {
            $this->resetAlternativesMatrix();
        }

        return Session::get(self::SESSION_KEY_ALTERNATIVES, []);
    }

    /**
     * Set pairwise matrix alternatif ke session
     */
    public function setAlternativesMatrix(array $matrices): void
    {
        $this->extendSession();
        Session::put(self::SESSION_KEY_ALTERNATIVES, $matrices);
    }

    /**
     * Get pairwise matrix untuk kriteria tertentu
     */
    public function getAlternativesMatrixByCriteria(string $criteriaName): array
    {
        $matrices = $this->getAlternativesMatrix();
        return $matrices[$criteriaName] ?? [];
    }

    /**
     * Set pairwise matrix untuk kriteria tertentu
     */
    public function setAlternativesMatrixByCriteria(string $criteriaName, array $matrix): void
    {
        $matrices = $this->getAlternativesMatrix();
        $matrices[$criteriaName] = $matrix;
        $this->setAlternativesMatrix($matrices);
    }

    /**
     * Update nilai tertentu pada pairwise alternatif matrix
     */
    public function updateAlternativeValue(string $criteriaName, int $row, int $col, float $value): void
    {
        $matrix = $this->getAlternativesMatrixByCriteria($criteriaName);

        if (empty($matrix)) {
            // Initialize matrix jika belum ada
            $alternatives = $this->configService->getAlternatives();
            $size = count($alternatives);
            $matrix = array_fill(0, $size, array_fill(0, $size, 1.0));
        }

        $matrix[$row][$col] = $value;
        $matrix[$col][$row] = $value !== 0 ? 1 / $value : 0;

        $this->setAlternativesMatrixByCriteria($criteriaName, $matrix);
    }

    /**
     * Reset pairwise alternatif ke default (semua 1)
     */
    public function resetAlternativesMatrix(): void
    {
        $recommendation = $this->recommendationService();

        $this->setAlternativesMatrix(
            $recommendation->getDefaultAlternativeMatrices()
        );

        // $alternatives = $this->configService->getAlternatives();
        // $criteria = $this->configService->getCriteria();

        // $matrices = [];
        // $size = count($alternatives);

        // foreach ($criteria as $criterion) {
        //     $matrices[$criterion] = array_fill(0, $size, array_fill(0, $size, 1.0));
        // }

        // $this->setAlternativesMatrix($matrices);
    }

    /**
     * Clear semua session
     */
    public function clearAllSession(): void
    {
        Session::forget([
            self::SESSION_KEY_CRITERIA,
            self::SESSION_KEY_ALTERNATIVES,
            self::SESSION_KEY_TIMESTAMP,
            self::SESSION_KEY_DATA_HASH,
        ]);
    }

    /**
     * Force reset untuk menggunakan skor kesesuaian baru
     * Dipanggil ketika ada perubahan signifikan pada sistem scoring
     */
    public function forceResetForNewScoring(): void
    {
        $this->clearAllSession();
        $this->ensureSessionExists();
    }

    /**
     * Get session info untuk debug
     */
    public function getSessionInfo(): array
    {
        $timestamp = Session::get(self::SESSION_KEY_TIMESTAMP);
        $elapsed = $timestamp ? now()->timestamp - $timestamp : 0;
        $remaining = max(0, self::SESSION_TIMEOUT - $elapsed);

        return [
            'is_expired' => $this->isSessionExpired(),
            'elapsed_seconds' => $elapsed,
            'remaining_seconds' => $remaining,
            'remaining_minutes' => ceil($remaining / 60),
            'criteria_matrix_set' => Session::has(self::SESSION_KEY_CRITERIA),
            'alternatives_matrix_set' => Session::has(self::SESSION_KEY_ALTERNATIVES),
        ];
    }

    protected function recommendationService(): AhpRecommendationService
    {
        $criteria = $this->configService->getCriteria();

        $matrix = $this->configService->getDefaultPairwiseMatrix();

        $ahp = new AhpService(
            $criteria,
            $matrix,
            1.12
        );

        $suitabilityService = new \App\Services\LandSuitabilityService($this->configService);

        return (new AhpRecommendationService($ahp, $this->configService, $suitabilityService))
            ->loadRecords();
    }
}
