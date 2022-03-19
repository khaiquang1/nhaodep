<?php namespace App\Repositories;

use App\Models\Salesteam;
use App\Models\User;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;
use Sentinel;

class SalesTeamRepositoryEloquent extends BaseRepository implements SalesTeamRepository
{
    private $userRepository;

    /**
     * Specify Model class name.
     *
     * @return string
     */

    public function model()
    {
        return Salesteam::class;
    }

    /**
     * Boot up the repository, pushing criteria.
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    public function getAll()
    {
        $salesTeams = $this->model;
        return $salesTeams;
    }

    public function generateParams(){
        $this->userRepository = new UserRepositoryEloquent(app());
    }

    public function teamLeader()
    {
        return $this->model->teamLeader();
    }


    public function createTeam(array $data)
    {
        $this->generateParams();
        $user = $this->userRepository->getUser();
        $data['user_id']= $user->id;

        $team = collect($data)->except('team_members')->toArray();
        $salesTeam = $this->create($team);

        $salesTeam->members()->attach($data['team_members']);
    }

    public function updateTeam(array $data,$salesteam_id)
    {
        $this->generateParams();
        $team = collect($data)->except('team_members')->toArray();
        $salesTeam = $this->update($team,$salesteam_id);
        $salesTeam->members()->sync($data['team_members']);
    }

    public function deleteTeam($deleteteam)
    {
        $this->generateParams();
        $this->delete($deleteteam);
    }


    public function findTeam($team_id)
    {
        $team=$this->with('members')->find($team_id);
        return $team;
    }

    public function getAllQuotations()
    {
        $salesTeams = $this->model->where('quotations', 1);
        return $salesTeams;
    }

    public function getAllLeads()
    {
        $salesTeams = $this->model->where('leads', 1);
        return $salesTeams;
    }

    public function getAllOpportunities()
    {
        $salesTeams = $this->model->where('opportunities', 1);
        return $salesTeams;
    }
}