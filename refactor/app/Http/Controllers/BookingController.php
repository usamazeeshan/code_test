<?php

namespace DTApi\Http\Controllers;

// represents a job model that contains information about a translation job.
use DTApi\Models\Job;                           
// represents a distance model that contains information about the distance and time taken to complete a translation job.
use DTApi\Models\Distance;                      
// represents a repository for accessing and managing translation job data.
use DTApi\Repository\BookingRepository;         
// represents a namespace for HTTP request classes.
use DTApi\Http\Requests;                        
// represents a class for handling HTTP requests in Laravel.
use Illuminate\Http\Request;                    

/**
 * Class BookingController
 * @package DTApi\Http\Controllers
 */
 
class BookingController extends Controller
{
    private $repository;
     /**
     * BookingController constructor.
     * @param BookingRepository $bookingRepository
     */
    // The  class into the current class so that its methods can use the functionality provided by the BookingRepository class
    public function __construct(BookingRepository $bookingRepository)
    {
        $this->repository = $bookingRepository;
    }

    /**
     * This is the index method of the JobsController class. 
     * It accepts a Request object as a parameter.
     *
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        if (!$user) {

            // If the user is not found, return a 404 response with an error message
            return response('User not found', 404);

        } elseif ($request->user()->isAdmin()) {

            // If the authenticated user is an admin, get all jobs and store the response
            $response = $this->repository->getAll($request);

        } else {
           // If a user_id is provided in the request, get all jobs for that user and store the response
           $response = $this->repository->getUsersJobs($user_id);
        }
        return response($response);
    }

    /**
     * This method returns a single Job object by its ID and loads the related translator and user data
     * @param  int  $job
     * @return Response
     */
    public function show(Job $job)
    {
        // Load the related translator and user data using the translatorJobRel relationship
        $job->load('translatorJobRel.user');

        // Return the Job object in a response
        return response($job);
    }


    /**
     * This method stores a new job based on the data provided in the request.
     * @param  Request  $request
     * @return mixed
     */
    public function store(Request $request)
    {
        // Get all the data from the request
        $data = $request->all();
        
        // Store the job in the repository and get the response
        $response = $this->repository->store($request->user(), $data);
        
        // Return the response as an HTTP response
        return response($response);
    }
    
      /**
     * This is the update method which takes a Job instance and a Request instance as parameters.   Returns  the result of the updateJob() method from the repository.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Job  $job
     * @return \Illuminate\Http\Response
     */
    public function update(Job $job, Request $request)
    {
        // Get all request data
        $data = $request->all();
        
        // Update the job with the given id using data from the request, excluding '_token' and 'submit' fields.
        $response = $this->repository->updateJob($job->id, array_except($data, ['_token', 'submit']), $request->user());
        
        // Return the response as an HTTP response
        return response($response);
    }

    /**
     *  This function returns a response from the storeJobEmail method of the repository.
     *
     * @param  Request  $request
     * 
     * @return \Illuminate\Http\Response
     */
    public function immediateJobEmail(Request $request)
    {
        // Get all the data from the request
        $data = $request->all();
        
        // Store the job email in the repository and get the response
        $response = $this->repository->storeJobEmail($data);
        
        // Return the response as an HTTP response
        return response($response);
    }
    
    /**
     * This function returns a response from the getUsersJobsHistory method of the repository, if the user_id is provided in the request. Otherwise, it returns null
     *
     * @param  Request $request
     * @return \Illuminate\Http\Response
     */
    public function getHistory(Request $request)
    {
        $user = $request->user();
        if (!$user) {

            // If the user is not found, return a 404 response with an error message
            return response('User not found', 404);

        } elseif ($user_id = $request->get('user_id')) {
            // If a user_id is provided in the request, get the job history for that user and store the response
            $response = $this->repository->getUsersJobsHistory($user_id, $request);
            
            // Return the response as an HTTP response
            return response($response);
        }
        
        // Return null if no user_id is provided in the request
        return null;
    }

    /**
     * This function returns a response from the acceptJob method of the repository.
     *
     * @param  Request $request
     * @return \Illuminate\Http\Response
     */
    public function acceptJob(Request $request)
    {
        // Get all request data
        $data = $request->all();
        
        // Accept the job with the given id using data from the request and the authenticated user
        $response = $this->repository->acceptJob($data, $request->user());
        
        // Return the response as an HTTP response
        return response($response);
    }

    
    /**
     * Accepts a job with a given ID and returns a response.
     *
     * @param Request $request
     * @return Response
     */
    public function acceptJobWithId(Request $request)
    {
        $data = $request->get('job_id');
        $response = $this->repository->acceptJobWithId($data, $request->user());
        return response($response);
    }
    
    // Cancels a job and returns a response.
    public function cancelJob(Request $request)
    {
        $data = $request->all();
        $response = $this->repository->cancelJobAjax($data, $request->user());
        return response($response);
    }

    /**
     * Cancel a job
     * 
     * @param  Request  $request  The HTTP request object containing the job data to cancel
     * @return Response           The HTTP response object containing the cancellation result
     */
    public function endJob(Request $request)
    {
        $data = $request->all();
        $response = $this->repository->endJob($data);
        return response($response);
    }

     /**
     * Notifies that the customer did not call for a job and returns a response.
     *
     * @param Request $request
     * @return Response
     */
    public function customerNotCall(Request $request)
    {
        $data = $request->all();

        // Pass the data to the repository to handle the customer not call situation
        $response = $this->repository->customerNotCall($data);

        // Return the response as a HTTP response
        return response($response);
    }

    // Returns a response with potential jobs for a user.
    public function getPotentialJobs(Request $request)
    {
        $user = $request->user();
        $response = $this->repository->getPotentialJobs($user);
        return response($response);
    }

     /**
     * Updates job distance, time, and admin-related fields based on request data
     *
     * @param Request $request
     * @return mixed
     */
    public function distanceFeed(Request $request)
    {
        // Extract relevant data from request
        $data = $request->only(['distance', 'time', 'jobid', 'session_time', 'flagged', 'admincomment', 'manually_handled', 'by_admin']);

        // Set variables with default empty values if not provided in request
        $distance = $data['distance'] ?? '';
        $time = $data['time'] ?? '';
        $jobid = $data['jobid'];
        $session = $data['session_time'] ?? '';
        $admincomment = $data['admincomment'] ?? '';

        // Set flagged, manually_handled, and by_admin variables based on request data
        if ($data['flagged'] === 'true' && $data['admincomment'] !== '') {
            $flagged = 'yes';
        } else {
            $flagged = 'no';
        }
        $manually_handled = ($data['manually_handled'] === 'true') ? 'yes' : 'no';
        $by_admin = ($data['by_admin'] === 'true') ? 'yes' : 'no';

        // Update distance and time fields in the Distance table if provided in request
        if ($time || $distance) {
            Distance::where('job_id', '=', $jobid)->update([
                'distance' => $distance,
                'time' => $time
            ]);
        }

        // Update admin-related fields in the Job table if provided in request
        if ($admincomment || $session || $flagged || $manually_handled || $by_admin) {
            Job::where('id', '=', $jobid)->update([
                'admin_comments' => $admincomment,
                'flagged' => $flagged,
                'session_time' => $session,
                'manually_handled' => $manually_handled,
                'by_admin' => $by_admin
            ]);
        }

        // Return a response indicating the record was updated
        return response('Record updated!');
    }

    
    /**
     * Reopens a job and returns a response.
     *
     * @param Request $request
     * @return Response
     */
    public function reopen(Request $request)
    {
        // Get all data
        $data = $request->all();
        // Get value data with method reopen
        $response = $this->repository->reopen($data);

        return response($response);
    }

    
    /**
     *  Resends push notifications for a job and returns a response.
     *
     * @param Request $request
     * @return Response
     */
    public function resendNotifications(Request $request)
    {
        $data = $request->all();
        
        // Find the job using its ID
        $job = $this->repository->find($data['jobid']);
        
        // Convert the job data to a format suitable for sending notifications
        $job_data = $this->repository->jobToData($job);
        
        // Send push notifications for the job to all translators
        $this->repository->sendNotificationTranslator($job, $job_data, '*');

        return response(['success' => 'Push sent']);
    }

    /**
     *  Resends SMS notifications for a job and returns a response.
     *
     * @param Request $request
     * @return Response
     */
    public function resendSMSNotifications(Request $request)
    {
        $data = $request->all();
        // Find the job using its ID
        $job = $this->repository->find($data['jobid']);

        // Convert the job data to a format suitable for sending SMS notifications
        $job_data = $this->repository->jobToData($job);

        try {
            // Send SMS notifications for the job to the assigned translator
            $this->repository->sendSMSNotificationToTranslator($job);

            return response(['success' => 'SMS sent']);
        } catch (\Exception $e) {
            // If there is an error, return an error response
            return response(['success' => $e->getMessage()]);
        }
    }
}