<?php

class TrialTest extends CDbTestCase
{
    public $fixtures = array(
        'user' => 'User',
        'trial' => 'Trial',
        'patient' => 'Patient',
        'trial_patient' => 'TrialPatient',
        'user_trial_permission' => 'UserTrialPermission',
    );

    public static function setupBeforeClass()
    {
        Yii::app()->getModule('OETrial');
    }

    public function testTitle()
    {
        $trial = new Trial();
        $trial->name = null;
        $this->assertFalse($trial->save(), 'A Trial cannot be saved with a null name');
    }

    public function testCreatedDate()
    {
        $trial = new Trial();
        $trial->started_date = date('Y-m-d', strtotime('2012-12-21'));
        $this->assertEquals('21 Dec 2012', $trial->getStartedDateForDisplay());

        $trial->started_date = date('Y-m-d', strtotime('1972-1-1'));
        $this->assertEquals('1 Jan 1972', $trial->getStartedDateForDisplay());

        $trial->started_date = null;
        $this->assertEquals('Pending', $trial->getStartedDateForDisplay());
    }

    public function testClosedDate()
    {
        $trial = new Trial();
        $trial->started_date = date('Y-m-d', strtotime('1970-01-01'));
        $trial->closed_date = date('Y-m-d', strtotime('2012-12-21'));
        $this->assertEquals($trial->getClosedDateForDisplay(), '21 Dec 2012');

        $trial->closed_date = date('Y-m-d', strtotime('1972-1-1'));
        $this->assertEquals('1 Jan 1972', $trial->getClosedDateForDisplay());

        $trial->started_date = null;
        $trial->closed_date = null;
        $this->assertNull($trial->getClosedDateForDisplay());

        $trial->started_date = date('Y-m-d', strtotime('1972-01-01'));
        $trial->closed_date = null;
        $this->assertEquals('present', $trial->getClosedDateForDisplay());
    }

    public function testDataProvidersExist()
    {
        $providers = $this->trial('trial1')->getPatientDataProviders(null, null);
        $this->assertArrayHasKey(TrialPatient::STATUS_ACCEPTED, $providers);

        $this->assertGreaterThan(0, count($providers), 'There should be at least one data provider returned');

        foreach (TrialPatient::getAllowedStatusRange() as $status) {
            $this->assertArrayHasKey($status, $providers, 'A data provider of each patient status should be returned');
        }
    }

    public function testDataProviderContent()
    {
        $providers = $this->trial('trial1')->getPatientDataProviders(null, null);

        /* @var CActiveDataProvider $shortlistedPatientProvider */
        $shortlistedPatientProvider = $providers[TrialPatient::STATUS_SHORTLISTED];
        $data = $shortlistedPatientProvider->getData();
        $this->assertCount(2, $data, 'Trial1 should have exactly 2 shortlisted patients');
    }

    public function testNoPatientsInDataProvider()
    {
        $providers = $this->trial('trial2')->getPatientDataProviders(null, null);

        /* @var CActiveDataProvider $shortlistedPatientProvider */
        $shortlistedPatientProvider = $providers[TrialPatient::STATUS_SHORTLISTED];
        $data = $shortlistedPatientProvider->getData();
        $this->assertCount(0, $data, 'Trial2 should have no shortlisted patients');
    }

    public function testDataProviderNameOrdering()
    {
        $shortlistedPatientProvider = $this->trial('trial1')->getPatientDataProvider(TrialPatient::STATUS_SHORTLISTED,
            'name', 'asc');
        $data = $shortlistedPatientProvider->getData();
        $this->assertCount(2, $data, 'There should be two patients in trial1');

        $this->assertLessThan($data[1]->patient->last_name, $data[0]->patient->last_name,
            'The list of patients should be sorted alphabetically by last name');
    }

    public function testDataProviderNameOrderingDesc()
    {
        $shortlistedPatientProvider = $this->trial('trial1')->getPatientDataProvider(TrialPatient::STATUS_SHORTLISTED,
            'name', 'desc');
        $data = $shortlistedPatientProvider->getData();
        $this->assertCount(2, $data, 'There should be two patients in trial1');

        $this->assertGreaterThan($data[1]->patient->last_name, $data[0]->patient->last_name,
            'The list of patients should be sorted alphabetically descending by last name');
    }

    public function testDataProviderAgeOrdering()
    {
        $shortlistedPatientProvider = $this->trial('trial1')->getPatientDataProvider(TrialPatient::STATUS_SHORTLISTED,
            'age', 'asc');
        $data = $shortlistedPatientProvider->getData();
        $this->assertCount(2, $data, 'There should be two patients in trial1');

        $this->assertLessThan($data[1]->patient->getAge(), $data[0]->patient->getAge(),
            'The list of patients should be sorted by age ascending');
    }

    public function testDataProviderAgeOrderingDesc()
    {
        $shortlistedPatientProvider = $this->trial('trial1')->getPatientDataProvider(TrialPatient::STATUS_SHORTLISTED,
            'age', 'desc');
        $data = $shortlistedPatientProvider->getData();
        $this->assertCount(2, $data, 'There should be two patients in trial1');

        $this->assertGreaterThan($data[1]->patient->getAge(), $data[0]->patient->getAge(),
            'The list of patients should be sorted by age descending');
    }

    public function testDataProviderExternalRefOrdering()
    {
        $shortlistedPatientProvider = $this->trial('trial1')->getPatientDataProvider(TrialPatient::STATUS_SHORTLISTED,
            'external_reference', 'asc');
        $data = $shortlistedPatientProvider->getData();
        $this->assertCount(2, $data, 'There should be two patients in trial1');

        $this->assertLessThan($data[1]->external_trial_identifier, $data[0]->external_trial_identifier,
            'The list of patients should be sorted by external id ascending');
    }

    public function testDataProviderExternalRefOrderingDesc()
    {
        $shortlistedPatientProvider = $this->trial('trial1')->getPatientDataProvider(TrialPatient::STATUS_SHORTLISTED,
            'external_reference', 'desc');
        $data = $shortlistedPatientProvider->getData();
        $this->assertCount(2, $data, 'There should be two patients in trial1');

        $this->assertGreaterThan($data[1]->external_trial_identifier, $data[0]->external_trial_identifier,
            'The list of patients should be sorted by external id descending');
    }

    public function testHasShortlistedPatients()
    {
        $this->assertTrue($this->trial('trial1')->hasShortlistedPatients(),
            'Trial1 should have at least one shortlisted patient');
        $this->assertFalse($this->trial('trial2')->hasShortlistedPatients(),
            'Trial2 should have no shortlisted patients');
    }

    public function testCheckTrialAccessManage()
    {
        $this->assertTrue(Trial::checkTrialAccess($this->user('user1'), $this->trial('trial1')->id,
            UserTrialPermission::PERMISSION_VIEW), 'user1 should have view access to trial1');

        $this->assertTrue(Trial::checkTrialAccess($this->user('user1'), $this->trial('trial1')->id,
            UserTrialPermission::PERMISSION_EDIT), 'user1 should have edit access to trial1');

        $this->assertTrue(Trial::checkTrialAccess($this->user('user1'), $this->trial('trial1')->id,
            UserTrialPermission::PERMISSION_MANAGE), 'user1 should have manage access to trial1');
    }

    public function testCheckTrialAccessView()
    {
        $this->assertTrue(Trial::checkTrialAccess($this->user('user2'), $this->trial('trial1')->id,
            UserTrialPermission::PERMISSION_VIEW), 'user2 should have view access to trial1');

        $this->assertFalse(Trial::checkTrialAccess($this->user('user2'), $this->trial('trial1')->id,
            UserTrialPermission::PERMISSION_EDIT), 'user2 should not have edit access to trial1');

        $this->assertFalse(Trial::checkTrialAccess($this->user('user2'), $this->trial('trial1')->id,
            UserTrialPermission::PERMISSION_MANAGE), 'user2 should not have manage access to trial1');
    }

    public function testCheckTrialAccessEdit()
    {
        $this->assertTrue(Trial::checkTrialAccess($this->user('user3'), $this->trial('trial1')->id,
            UserTrialPermission::PERMISSION_VIEW), 'user3 should have view access to trial1');

        $this->assertTrue(Trial::checkTrialAccess($this->user('user3'), $this->trial('trial1')->id,
            UserTrialPermission::PERMISSION_EDIT), 'user3 should not have edit access to trial1');

        $this->assertFalse(Trial::checkTrialAccess($this->user('user3'), $this->trial('trial1')->id,
            UserTrialPermission::PERMISSION_MANAGE), 'user3 should not have manage access to trial1');
    }

    public function testGetTrialAccess()
    {
        /* @var Trial $trial */
        $trial = $this->trial('trial1');
        $this->assertEquals(UserTrialPermission::PERMISSION_MANAGE, $trial->getTrialAccess($this->user('user1')));
        $this->assertEquals(UserTrialPermission::PERMISSION_VIEW, $trial->getTrialAccess($this->user('user2')));
        $this->assertEquals(UserTrialPermission::PERMISSION_EDIT, $trial->getTrialAccess($this->user('user3')));
    }

    public function testAddPatient()
    {
        /* @var Trial $trial */
        $trial = $this->trial('trial1');
        $patient = $this->patient('patient2');
        $trialPatient = $trial->addPatient($patient, TrialPatient::STATUS_SHORTLISTED);

        $this->assertNotNull($trialPatient, 'The patient should have been added to the trial');
        $this->assertEquals(TrialPatient::STATUS_SHORTLISTED, $trialPatient->patient_status,
            'The patietn status should be shortlisted');
        $this->assertEquals($trial->id, $trialPatient->trial->id, 'The trial id should match the patient trial id');
        $this->assertEquals(TrialPatient::TREATMENT_TYPE_UNKNOWN, $trialPatient->treatment_type,
            'The patient treatment type should start at unknown');
    }

    public function testRemovePatient()
    {
        /* @var TrialPatient $trialPatient */
        $trialPatient = $this->trial_patient('trial_patient_1');

        /* @var Trial $trial */
        $trial = $this->trial('trial1');
        $patient = $this->patient('patient1');

        $this->assertNotNull(TrialPatient::model()->find('trial_id = :trialId AND patient_id = :patientId',
            array(
                ':trialId' => $trial->id,
                ':patientId' => $patient->id,
            )
        ), 'The patient should have started in the trial');

        $trial->removePatient($trialPatient->patient_id);

        $this->assertNull(TrialPatient::model()->find('trial_id = :trialId AND patient_id = :patientId',
            array(
                ':trialId' => $trial->id,
                ':patientId' => $patient->id,
            )
        ), 'The patient should no longer be in the trial');
    }

    public function testAddUserPermission()
    {
        /* @var Trial $trial2 */
        $trial2 = $this->trial('trial2');
        /* @var User $user2 */
        $user2 = $this->user('user2');

        $result = $trial2->addUserPermission($user2->id, UserTrialPermission::PERMISSION_VIEW, null);
        $this->assertEquals(Trial::RETURN_CODE_USER_PERMISSION_OK, $result,
            'The permission should have been added successfully');
    }

    public function testAddUserPermissionClash()
    {
        /* @var Trial $trial */
        $trial = $this->trial('trial1');
        $user1 = $this->user('user1');

        $result = $trial->addUserPermission($user1->id, UserTrialPermission::PERMISSION_VIEW, null);
        $this->assertEquals(Trial::RETURN_CODE_USER_PERMISSION_ALREADY_EXISTS, $result,
            'The permission already exists, and a duplicate should have been prevented');
    }

    public function testRemoveUserPermission()
    {
        /* @var Trial $trial */
        $trial = $this->trial('trial1');
        /* @var UserTrialPermission $userPermission */
        $userPermission = $this->user_trial_permission('user_trial_permission_2');

        $this->assertEquals(Trial::REMOVE_PERMISSION_RESULT_SUCCESS, $trial->removeUserPermission($userPermission->id),
            'The permission should have been removed successfully');
    }

    public function testRemoveLastUserPermission()
    {
        /* @var Trial $trial */
        $trial = $this->trial('trial1');
        /* @var UserTrialPermission $userPermission */
        $userPermission = $this->user_trial_permission('user_trial_permission_1');

        $this->assertEquals(Trial::REMOVE_PERMISSION_RESULT_CANT_REMOVE_LAST,
            $trial->removeUserPermission($userPermission->id), 'The last manager should not have been removable');
    }

    public function testTransitionStateShortlist()
    {
        /* @var Trial $trial */
        $trial = $this->trial('trial2');

        $this->assertEquals(Trial::STATUS_OPEN, $trial->status, 'The trial should have been open');
        $result = $trial->transitionState(Trial::STATUS_IN_PROGRESS);
        $this->assertEquals(Trial::RETURN_CODE_OK, $result,
            'The trial should have move to the in progress state');
    }

    public function testTransitionStateShortlistBlock()
    {
        /* @var Trial $trial */
        $trial = $this->trial('trial1');

        $this->assertEquals(Trial::STATUS_OPEN, $trial->status, 'The trial should have been open');
        $this->assertTrue($trial->hasShortlistedPatients(), 'The trial should have had shortlisted patients');
        $result = $trial->transitionState(Trial::STATUS_IN_PROGRESS);
        $this->assertEquals(Trial::RETURN_CODE_CANT_OPEN_SHORTLISTED_TRIAL, $result,
            'The trial should have been prevented from being started while it still has shortlisted patients');
    }

    public function testTransitionStateStartedDate()
    {
        /* @var Trial $trial */
        $trial = $this->trial('trial2');

        $this->assertEquals(Trial::STATUS_OPEN, $trial->status, 'The trial should have been open');
        $this->assertNull($trial->started_date, 'The started date should be null until the trial is started');

        $result = $trial->transitionState(Trial::STATUS_IN_PROGRESS);
        $this->assertNotNull($trial->started_date, 'The started date should be set when the trial is started');
    }

    public function testTransitionStateClosedDate()
    {
        /* @var Trial $trial */
        $trial = $this->trial('trial2');

        $trial->transitionState(Trial::STATUS_IN_PROGRESS);
        $this->assertNull($trial->closed_date, 'The closed date should be null until the trial is closed');

        $trial->transitionState(Trial::STATUS_CLOSED);
        $this->assertNotNull($trial->closed_date, 'The closed date should be set when the trial is closed');
    }
}
