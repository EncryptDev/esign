<?php

namespace App\Http\Controllers;

use App\Services\DocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    private DocumentService $documentService;

    public function __construct(DocumentService $documentService)
    {
        $this->documentService = $documentService;
    }

    /**
     * Display the dashboard
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Get document statistics
        $statistics = $this->documentService->getUserDocumentStatistics($user);

        // Get recent documents
        $recentDocuments = $this->documentService->getUserDocuments($user)
            ->take(5);

        // Get documents by status for quick access
        $draftDocuments = $this->documentService->getUserDocuments($user, 'draft')
            ->take(3);

        $signedDocuments = $this->documentService->getUserDocuments($user, 'signed')
            ->take(3);

        // Get recent activity (last 10 audit logs)
        $recentActivity = $user->auditLogs()
            ->with('auditable')
            ->latest('created_at')
            ->take(10)
            ->get();

        return view('dashboard', compact(
            'statistics',
            'recentDocuments',
            'draftDocuments',
            'signedDocuments',
            'recentActivity'
        ));
    }
}
