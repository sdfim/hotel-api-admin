<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UnmappedDataReport extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The unmapped data for the report.
     *
     * @var array
     */
    public $unmappedData;

    /**
     * The report date.
     *
     * @var string
     */
    public $date;

    /**
     * The path to the CSV file.
     *
     * @var string
     */
    public $csvPath;

    /**
     * The summary of the report.
     *
     * @var array
     */
    public $summary;

    /**
     * Create a new message instance.
     */
    public function __construct(array $unmappedData, string $date, string $csvPath)
    {
        $this->unmappedData = $unmappedData;
        $this->date = $date;
        $this->csvPath = $csvPath;
        
        $hotels = count($unmappedData);
        $rooms = collect($unmappedData)->sum(fn($h) => count($h['unmapped_rooms']));
        $rates = collect($unmappedData)->sum(fn($h) => count($h['unmapped_rates']));
        $this->summary = [
            'hotels' => $hotels,
            'rooms' => $rooms,
            'rates' => $rates,
        ];
    }

    public function build()
    {
        return $this->subject('Daily Report: Unmapped Rooms and Rates - ' . $this->date)
            ->view('emails.unmapped-data-report')
            ->attach($this->csvPath, [
                'as' => 'unmapped_data_report_' . $this->date . '.csv',
                'mime' => 'text/csv',
            ]);
    }
} 