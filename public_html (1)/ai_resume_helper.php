<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once 'token_tracker.php';

use OpenAI\Client;

class AIResumeHelper {
    private $client;
    private $model;
    private $tokenTracker;
    
    public function __construct($conn) {
        $this->model = AI_MODEL;
        $this->client = OpenAI::client(AI_API_KEY);
        $this->tokenTracker = new TokenTracker($conn);
    }

    private function checkAndRecordTokens($user_id, $prompt, $response) {
        // Estimate tokens (rough estimation)
        $prompt_tokens = strlen($prompt) / 4;
        $response_tokens = strlen($response) / 4;
        $total_tokens = $prompt_tokens + $response_tokens;
        
        if (!$this->tokenTracker->checkUserTokenLimit($user_id)) {
            throw new Exception("Daily token limit reached. Please try again tomorrow.");
        }
        
        $this->tokenTracker->recordTokenUsage($user_id, $total_tokens);
        return true;
    }

    /**
     * Enhance work experience with action verbs and quantifiable achievements
     */
    public function enhanceWorkExperience($experience, $role, $user_id) {
        $prompt = "As a professional resume writer, enhance the following work experience for a {$role} position. 
                  Use strong action verbs, add quantifiable achievements where possible, and focus on relevant accomplishments. 
                  Keep the same basic information but make it more impactful:

                  Original experience:
                  {$experience}";

        try {
            $response = $this->client->chat()->create([
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a professional resume writer with expertise in enhancing work experience.'],
                    ['role' => 'user', 'content' => $prompt]
                ],
                'temperature' => 0.7,
                'max_tokens' => 500
            ]);
            
            $result = $response->choices[0]->message->content;
            $this->checkAndRecordTokens($user_id, $prompt, $result);
            return $result;
        } catch (Exception $e) {
            error_log("OpenAI API Error: " . $e->getMessage());
            return "Error: " . $e->getMessage();
        }
    }

    /**
     * Generate a professional summary based on experience and target role
     */
    public function generateProfessionalSummary($experience, $skills, $role, $user_id) {
        $prompt = "Create a compelling professional summary for a {$role} position based on the following experience and skills:

                  Experience:
                  {$experience}

                  Skills:
                  {$skills}

                  Write a concise, powerful summary that highlights relevant achievements and skills for the {$role} position.";

        try {
            $response = $this->client->chat()->create([
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a professional resume writer specializing in creating impactful professional summaries.'],
                    ['role' => 'user', 'content' => $prompt]
                ],
                'temperature' => 0.7,
                'max_tokens' => 500
            ]);
            
            $result = $response->choices[0]->message->content;
            $this->checkAndRecordTokens($user_id, $prompt, $result);
            return $result;
        } catch (Exception $e) {
            error_log("OpenAI API Error: " . $e->getMessage());
            return "Error: " . $e->getMessage();
        }
    }

    /**
     * Optimize skills section for ATS and target role
     */
    public function optimizeSkills($skills, $role, $user_id) {
        $prompt = "As an ATS optimization expert, analyze and reorganize these skills for a {$role} position:

                  Skills:
                  {$skills}

                  Return a comma-separated list of skills, prioritizing those most relevant for the role, 
                  including both hard and soft skills, and ensuring ATS-friendly formatting.";

        try {
            $response = $this->client->chat()->create([
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => 'You are an ATS optimization expert specializing in resume skills.'],
                    ['role' => 'user', 'content' => $prompt]
                ],
                'temperature' => 0.7,
                'max_tokens' => 500
            ]);
            
            $result = $response->choices[0]->message->content;
            $this->checkAndRecordTokens($user_id, $prompt, $result);
            return $result;
        } catch (Exception $e) {
            error_log("OpenAI API Error: " . $e->getMessage());
            return "Error: " . $e->getMessage();
        }
    }

    /**
     * Enhance achievements to be more impactful
     */
    public function enhanceAchievements($achievements, $role, $user_id) {
        $prompt = "Enhance the following achievements to be more impactful for a {$role} position. 
                  Add metrics where possible and focus on relevant outcomes:

                  Original achievements:
                  {$achievements}";

        try {
            $response = $this->client->chat()->create([
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a professional resume writer specializing in enhancing achievements.'],
                    ['role' => 'user', 'content' => $prompt]
                ],
                'temperature' => 0.7,
                'max_tokens' => 500
            ]);
            
            $result = $response->choices[0]->message->content;
            $this->checkAndRecordTokens($user_id, $prompt, $result);
            return $result;
        } catch (Exception $e) {
            error_log("OpenAI API Error: " . $e->getMessage());
            return "Error: " . $e->getMessage();
        }
    }

    /**
     * Generate improvement suggestions for the resume
     */
    public function generateSuggestions($resume_content, $role, $user_id) {
        $prompt = "As a professional resume reviewer, analyze this resume content for a {$role} position and provide specific suggestions for improvement:

                  Resume content:
                  {$resume_content}

                  Provide actionable suggestions for:
                  1. Content improvements
                  2. Format and structure
                  3. Keywords and ATS optimization
                  4. Areas that need more detail or metrics";

        try {
            $response = $this->client->chat()->create([
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a professional resume reviewer with expertise in ATS optimization and modern resume practices.'],
                    ['role' => 'user', 'content' => $prompt]
                ],
                'temperature' => 0.7,
                'max_tokens' => 500
            ]);
            
            $result = $response->choices[0]->message->content;
            $this->checkAndRecordTokens($user_id, $prompt, $result);
            return $result;
        } catch (Exception $e) {
            error_log("OpenAI API Error: " . $e->getMessage());
            return "Error: " . $e->getMessage();
        }
    }
} 