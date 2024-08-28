<?php
require("config/env.php");


if($route == '/user/interviews/all'):
    $seo = array(
        'title' => 'interviews',
        'description' => 'CRM',
        'keywords' => 'User Panel'
    );
    echo $twig->render('user/interviews/all.twig', ['seo' => $seo]);
endif;

if($route == '/user/interviews/questions'):
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

// Handle form submission
            $csrf = $_POST['csrf'];
            $sectionTitle = $_POST['section_title'];
            $questions = $_POST['questions'];
            // Validate CSRF token here
            // Insert section into database
            $stmt = $h->insert('sections')->values([ 'title' => $sectionTitle])->run();
            $sectionId = $stmt;

            // Insert questions into database
            foreach ($questions as $question) {

                $isRequired = isset($question['required']) ? 1 : 0;
                $stmt1 = $h->insert('questions')->values([ 'section_id' => $sectionId,'question' => $question['question'],'type' => $question['type'],'required' => $isRequired])->run();
                 $questionId = $stmt1;

                // Insert options if applicable
                if (isset($question['options']) && !empty($question['options'])) {
                    foreach ($question['options'] as $option) {
                        if(!empty($option)){
                            $stmt = $h->insert('options')->values([ 'question_id' => $questionId,'option_text' => $option])->run();
                        }
                    }
                }
            }
            echo '1';
            exit();
    }else{
        $seo = array(
            'title' => 'Question',
            'description' => 'CRM',
            'keywords' => 'User Panel'
        );
        echo $twig->render('user/interviews/questions.twig', ['seo' => $seo]);
    }

endif;

if($route == '/user/interviews/questions/update/$sectionId'):
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['section_id'])) {
        $csrf = $_POST['csrf'];
        $sectionId = $_POST['section_id'];
        $sectionTitle = $_POST['section_title'];
        $questions = $_POST['questions'];
        $removedQuestions = $_POST['removed_questions'] ?? [];

        try {
            // Update section in the database
            $h->update('sections')->values(['title' => $sectionTitle])->where('id', '=', $sectionId)->run();

            // Remove deleted questions
            foreach ($removedQuestions as $questionId) {
                $h->table('questions')->delete()->where('id', '=', $questionId)->run();
                $h->table('options')->delete()->where('question_id', '=', $questionId)->run(); // Also delete associated options
            }

            // Update or insert questions in the database
            foreach ($questions as $question) {
                if (!empty($question['question']) && !empty($question['type'])) {
                    if (!empty($question['id'])) {
                        // Update existing question
                        $h->update('questions')->values([
                            'question' => $question['question'],
                            'type' => $question['type'],
                            'required' => isset($question['required']) ? 1 : 0
                        ])->where('id', '=', $question['id'])->run();

                        // Remove all existing options for this question
                        $h->table('options')->delete()->where('question_id', '=', $question['id'])->run();

                        // Insert new options
                        if (isset($question['options'])) {
                            foreach ($question['options'] as $option) {
                                if (!empty($option)) {
                                    $h->insert('options')->values(['question_id' => $question['id'], 'option_text' => $option])->run();
                                }
                            }
                        }
                    } else {
                        // Insert new question
                        $questionId=$h->insert('questions')->values([
                            'section_id' => $sectionId,
                            'question' => $question['question'],
                            'type' => $question['type'],
                            'required' => isset($question['required']) ? 1 : 0
                        ])->run();
//                        $questionId = $h->lastInsertId(); // Retrieve the ID of the newly inserted question

                        // Insert new options

                        if (isset($question['options'])) {
                            foreach ($question['options'] as $option) {
                                if (!empty($option)) {
                                    $h->insert('options')->values(['question_id' => $questionId, 'option_text' => $option])->run();
                                }
                            }
                        }
                    }
                }
            }

            echo 'Form updated successfully!';
        } catch (Exception $e) {
            echo 'Something went wrong. Please try again.';

        }
    }else{
        $seo = array(
            'title' => 'Question',
            'description' => 'CRM',
            'keywords' => 'User Panel'
        );



        $section = $h->table('sections')->select()->where('id', '=', $sectionId)->fetchAll();
        $questions = $h->table('questions')->select()->where('section_id', '=', $sectionId)->fetchAll();
        foreach ($questions as &$question) {
            $question['options'] = $h->table('options')->select()->where('question_id', '=', $question['id'])->fetchAll();
        }
        $section['questions'] = $questions;

        echo $twig->render('user/interviews/questions_update.twig', [
            'seo' => $seo,
            'section' => $section

        ]);
    }


    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['question_id_delete'])) {
        $question_id_delete = $_POST['question_id_delete'];

        $h->table('options')->delete()->where('question_id', '=', $question_id_delete)->run();
        echo 'Options removed successfully!';
    }

endif;