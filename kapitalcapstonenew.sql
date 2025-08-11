-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 07, 2025 at 02:16 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `kapitalcapstone`
--

-- --------------------------------------------------------

--
-- Table structure for table `ai_conversations`
--

CREATE TABLE `ai_conversations` (
  `conversation_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `question` text NOT NULL,
  `response` text DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `responded_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ai_conversations`
--

INSERT INTO `ai_conversations` (`conversation_id`, `user_id`, `question`, `response`, `created_at`, `responded_at`) VALUES
(1, 1, 'How can I attract potential investors?', '', '2025-08-07 07:58:42', NULL),
(2, 1, 'What are effective strategies for market research?', '', '2025-08-07 08:02:25', NULL),
(3, 1, 'How can I attract potential investors?', '', '2025-08-07 08:04:25', NULL),
(4, 1, 'What are effective strategies for market research?', '## Effective Market Research Strategies\r\n\r\nUnderstanding your market is crucial for startup success. Here are proven research methods:\r\n\r\n### 1. Primary Research Methods\r\n- **Surveys and Questionnaires**: Use tools like Google Forms or SurveyMonkey\r\n- **Customer Interviews**: Conduct 1-on-1 conversations with potential customers\r\n- **Focus Groups**: Gather 6-8 people for structured discussions\r\n- **Observation Studies**: Watch how customers behave in natural settings\r\n\r\n### 2. Secondary Research Sources\r\n- **Industry Reports**: Use IBISWorld, Statista, or government databases\r\n- **Competitor Analysis**: Study competitors\' websites, pricing, and customer reviews\r\n- **Social Media Monitoring**: Track mentions and conversations about your industry\r\n- **Academic Research**: Access studies from universities and research institutions\r\n\r\n### 3. Digital Tools for Market Research\r\n- **Google Trends**: Analyze search patterns and seasonal trends\r\n- **Social Media Analytics**: Use native tools or third-party platforms\r\n- **SEO Tools**: Analyze keyword search volumes and competition\r\n- **Customer Review Mining**: Extract insights from review platforms\r\n\r\n### 4. Key Metrics to Track\r\n- Market size (TAM, SAM, SOM)\r\n- Customer acquisition cost (CAC)\r\n- Customer lifetime value (CLV)\r\n- Market growth rate and trends\r\n\r\n**Remember:** Combine multiple research methods for the most accurate picture of your market!', '2025-08-07 08:05:19', '2025-08-07 08:05:19'),
(5, 1, 'How do I identify my target market?', '### Identifying Your Target Market\n\nIdentifying your target market is crucial for tailoring your marketing strategies and improving product development. Here’s a step-by-step guide:\n\n#### 1. **Conduct Market Research**\n   - **Surveys and Questionnaires**: Create surveys to gather data on potential customers’ preferences, behaviors, and demographics.\n     - Example: Use tools like SurveyMonkey or Google Forms to collect insights.\n   - **Focus Groups**: Organize focus groups with individuals who fit your potential customer profile to gain qualitative insights.\n\n#### 2. **Analyze Demographics**\n   - **Age, Gender, Income Level**: Identify who would most likely use your product/service.\n     - Example: If you’re launching a luxury skincare line, your target demographic may be women aged 25-45 with disposable income.\n   - **Location**: Determine if your product has a geographic limitation or specific regions where demand is higher.\n\n#### 3. **Understand Psychographics**\n   - **Lifestyle & Interests**: Analyze the values, interests, and lifestyle of potential customers.\n     - Example: If you’re selling eco-friendly products, target consumers who prioritize sustainability in their lifestyle choices.\n\n#### 4. **Segment Your Market**\n   - Divide the broader market into smaller segments based on shared characteristics:\n     - **Behavioral Segmentation**: Based on purchasing behavior (e.g., brand loyalty).\n     - **Geographic Segmentation**: By region or city where the product may be popular.\n\n#### 5. **Competitor Analysis**\n   - Study competitors to understand their target markets.\n     - Example: Analyze who engages with their social media or reviews their products online. Tools like SEMrush can help identify competitor demographics.\n\n#### 6. **Create Customer Personas**\n   - Develop detailed profiles representing different segments of your target audience including:\n     - Name\n     - Age\n     - Occupation\n     - Interests\n     - Pain points\n   - Example persona for a fitness app:\n     ```\n     Name: Sarah\n     Age: 30\n     Occupation: Marketing Manager\n     Interests: Yoga, healthy eating, technology\n     Pain Points: Struggles to find time for workouts due to her busy schedule.\n     ```\n\n#### 7. **Test and Validate**\n   - Launch a minimum viable product (MVP) or pilot campaign targeting different segments and analyze the response.\n   - Use A/B testing in marketing campaigns to see which segment responds best.\n\n### Conclusion\n\nIdentifying your target market involves a mix of quantitative data analysis and qualitative insights. By using surveys, segmenting demographics, analyzing competitors, and creating customer personas, you can effectively define your audience and tailor your marketing efforts accordingly. Implement these steps to refine your approach and ensure you\'re reaching the right people with your product or service.', '2025-08-07 08:11:23', '2025-08-07 08:11:35');

--
-- Triggers `ai_conversations`
--
DELIMITER $$
CREATE TRIGGER `prevent_zero_conversation_id` BEFORE INSERT ON `ai_conversations` FOR EACH ROW BEGIN
    IF NEW.conversation_id = 0 THEN
        SET NEW.conversation_id = NULL;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `ai_response_cache`
--

CREATE TABLE `ai_response_cache` (
  `id` int(11) NOT NULL,
  `question_hash` varchar(32) NOT NULL,
  `question` text NOT NULL,
  `response` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ai_response_cache`
--

INSERT INTO `ai_response_cache` (`id`, `question_hash`, `question`, `response`, `created_at`) VALUES
(1, '7f0d42beaed3f130455ec014d47968c4', 'How do I identify my target market?', '### Identifying Your Target Market\n\nIdentifying your target market is crucial for tailoring your marketing strategies and improving product development. Here’s a step-by-step guide:\n\n#### 1. **Conduct Market Research**\n   - **Surveys and Questionnaires**: Create surveys to gather data on potential customers’ preferences, behaviors, and demographics.\n     - Example: Use tools like SurveyMonkey or Google Forms to collect insights.\n   - **Focus Groups**: Organize focus groups with individuals who fit your potential customer profile to gain qualitative insights.\n\n#### 2. **Analyze Demographics**\n   - **Age, Gender, Income Level**: Identify who would most likely use your product/service.\n     - Example: If you’re launching a luxury skincare line, your target demographic may be women aged 25-45 with disposable income.\n   - **Location**: Determine if your product has a geographic limitation or specific regions where demand is higher.\n\n#### 3. **Understand Psychographics**\n   - **Lifestyle & Interests**: Analyze the values, interests, and lifestyle of potential customers.\n     - Example: If you’re selling eco-friendly products, target consumers who prioritize sustainability in their lifestyle choices.\n\n#### 4. **Segment Your Market**\n   - Divide the broader market into smaller segments based on shared characteristics:\n     - **Behavioral Segmentation**: Based on purchasing behavior (e.g., brand loyalty).\n     - **Geographic Segmentation**: By region or city where the product may be popular.\n\n#### 5. **Competitor Analysis**\n   - Study competitors to understand their target markets.\n     - Example: Analyze who engages with their social media or reviews their products online. Tools like SEMrush can help identify competitor demographics.\n\n#### 6. **Create Customer Personas**\n   - Develop detailed profiles representing different segments of your target audience including:\n     - Name\n     - Age\n     - Occupation\n     - Interests\n     - Pain points\n   - Example persona for a fitness app:\n     ```\n     Name: Sarah\n     Age: 30\n     Occupation: Marketing Manager\n     Interests: Yoga, healthy eating, technology\n     Pain Points: Struggles to find time for workouts due to her busy schedule.\n     ```\n\n#### 7. **Test and Validate**\n   - Launch a minimum viable product (MVP) or pilot campaign targeting different segments and analyze the response.\n   - Use A/B testing in marketing campaigns to see which segment responds best.\n\n### Conclusion\n\nIdentifying your target market involves a mix of quantitative data analysis and qualitative insights. By using surveys, segmenting demographics, analyzing competitors, and creating customer personas, you can effectively define your audience and tailor your marketing efforts accordingly. Implement these steps to refine your approach and ensure you\'re reaching the right people with your product or service.', '2025-08-07 00:11:35');

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--

CREATE TABLE `applications` (
  `application_id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `job_seeker_id` int(11) NOT NULL,
  `status` enum('applied','reviewed','interviewed','hired','not approved') DEFAULT 'applied',
  `cover_letter` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `applications`
--

INSERT INTO `applications` (`application_id`, `job_id`, `job_seeker_id`, `status`, `cover_letter`, `created_at`) VALUES
(1, 1, 4, 'interviewed', 'eme', '2025-08-04 23:31:30');

--
-- Triggers `applications`
--
DELIMITER $$
CREATE TRIGGER `application_status_update_notification` AFTER UPDATE ON `applications` FOR EACH ROW BEGIN
    INSERT INTO Notifications (user_id, sender_id, type, message, application_id, status)
    VALUES (
        NEW.job_seeker_id,
        (SELECT entrepreneur_id FROM Startups WHERE startup_id = (SELECT startup_id FROM Jobs WHERE job_id = NEW.job_id)),
        'application_status',
        CONCAT('The status of your application for job: ', (SELECT role FROM Jobs WHERE job_id = NEW.job_id), ' has been updated to: ', NEW.status),
        NEW.application_id,
        'unread'
    );
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `job_application_notification` AFTER INSERT ON `applications` FOR EACH ROW BEGIN
    INSERT INTO Notifications (user_id, sender_id, type, message, job_id, application_id, status)
    VALUES (
        (SELECT entrepreneur_id FROM Startups WHERE startup_id = (SELECT startup_id FROM Jobs WHERE job_id = NEW.job_id)),
        NEW.job_seeker_id,
        'application_status',
        CONCAT('Job seeker ', (SELECT name FROM Users WHERE user_id = NEW.job_seeker_id), ' applied for your job: ', (SELECT role FROM Jobs WHERE job_id = NEW.job_id)),
        NEW.job_id,
        NEW.application_id,
        'unread'
    );
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `prevent_zero_application_id` BEFORE INSERT ON `applications` FOR EACH ROW BEGIN
    IF NEW.application_id = 0 THEN
        SET NEW.application_id = NULL;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `conversation_requests`
--

CREATE TABLE `conversation_requests` (
  `request_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `conversation_requests`
--

INSERT INTO `conversation_requests` (`request_id`, `sender_id`, `receiver_id`, `status`, `created_at`, `updated_at`) VALUES
(2, 1, 3, 'approved', '2025-08-04 22:43:02', '2025-08-04 22:43:17'),
(3, 4, 1, 'pending', '2025-08-06 23:31:18', '2025-08-06 23:31:18');

--
-- Triggers `conversation_requests`
--
DELIMITER $$
CREATE TRIGGER `prevent_zero_request_id` BEFORE INSERT ON `conversation_requests` FOR EACH ROW BEGIN
    IF NEW.request_id = 0 THEN
        SET NEW.request_id = NULL;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_message_visibility` AFTER UPDATE ON `conversation_requests` FOR EACH ROW BEGIN
    IF OLD.status != NEW.status THEN
        UPDATE Messages 
        SET request_status = NEW.status
        WHERE sender_id = NEW.sender_id 
        AND receiver_id = NEW.receiver_id
        AND is_intro_message = TRUE;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `document_validation_rules`
--

CREATE TABLE `document_validation_rules` (
  `id` int(11) NOT NULL,
  `document_type` varchar(50) NOT NULL,
  `rule_name` varchar(100) NOT NULL,
  `rule_description` text DEFAULT NULL,
  `validation_regex` varchar(255) DEFAULT NULL,
  `is_required` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Triggers `document_validation_rules`
--
DELIMITER $$
CREATE TRIGGER `prevent_zero_validation_rule_id` BEFORE INSERT ON `document_validation_rules` FOR EACH ROW BEGIN
    IF NEW.id = 0 THEN
        SET NEW.id = NULL;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `document_verification_history`
--

CREATE TABLE `document_verification_history` (
  `id` int(11) NOT NULL,
  `document_id` int(11) NOT NULL,
  `previous_status` varchar(20) DEFAULT NULL,
  `new_status` varchar(20) DEFAULT NULL,
  `changed_by` int(11) DEFAULT NULL,
  `change_reason` text DEFAULT NULL,
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `document_verification_history`
--

INSERT INTO `document_verification_history` (`id`, `document_id`, `previous_status`, `new_status`, `changed_by`, `change_reason`, `changed_at`) VALUES
(1, 1, 'pending', 'approved', 2, NULL, '2025-08-04 22:09:52'),
(2, 2, 'pending', 'approved', 2, NULL, '2025-08-04 22:18:28'),
(3, 3, 'pending', 'approved', 2, NULL, '2025-08-04 23:30:30'),
(4, 4, 'pending', 'approved', 2, NULL, '2025-08-06 23:24:11');

--
-- Triggers `document_verification_history`
--
DELIMITER $$
CREATE TRIGGER `prevent_zero_verification_history_id` BEFORE INSERT ON `document_verification_history` FOR EACH ROW BEGIN
    IF NEW.id = 0 THEN
        SET NEW.id = NULL;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `entrepreneurs`
--

CREATE TABLE `entrepreneurs` (
  `entrepreneur_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `entrepreneurs`
--

INSERT INTO `entrepreneurs` (`entrepreneur_id`, `created_at`, `updated_at`) VALUES
(1, '2025-08-04 21:39:54', '2025-08-04 21:39:54'),
(2, '2025-08-04 22:07:08', '2025-08-04 22:07:08'),
(5, '2025-08-06 23:18:36', '2025-08-06 23:18:36');

--
-- Triggers `entrepreneurs`
--
DELIMITER $$
CREATE TRIGGER `prevent_zero_entrepreneur_id` BEFORE INSERT ON `entrepreneurs` FOR EACH ROW BEGIN
    IF NEW.entrepreneur_id = 0 THEN
        SET NEW.entrepreneur_id = NULL;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `entrepreneur_job_seeker_matches`
--

CREATE TABLE `entrepreneur_job_seeker_matches` (
  `match_id` int(11) NOT NULL,
  `entrepreneur_id` int(11) NOT NULL,
  `job_seeker_id` int(11) NOT NULL,
  `match_score` decimal(5,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `investors`
--

CREATE TABLE `investors` (
  `investor_id` int(11) NOT NULL,
  `investment_range_min` decimal(15,2) NOT NULL,
  `investment_range_max` decimal(15,2) NOT NULL,
  `preferred_industries` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`preferred_industries`)),
  `preferred_locations` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`preferred_locations`)),
  `funding_stage_preferences` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`funding_stage_preferences`)),
  `bio` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `investors`
--

INSERT INTO `investors` (`investor_id`, `investment_range_min`, `investment_range_max`, `preferred_industries`, `preferred_locations`, `funding_stage_preferences`, `bio`, `created_at`, `updated_at`) VALUES
(3, 0.00, 0.00, NULL, NULL, NULL, NULL, '2025-08-04 22:16:15', '2025-08-04 22:16:15');

--
-- Triggers `investors`
--
DELIMITER $$
CREATE TRIGGER `prevent_zero_investor_id` BEFORE INSERT ON `investors` FOR EACH ROW BEGIN
    IF NEW.investor_id = 0 THEN
        SET NEW.investor_id = NULL;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `job_id` int(11) NOT NULL,
  `startup_id` int(11) NOT NULL,
  `role` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `requirements` text DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `salary_range_min` decimal(15,2) DEFAULT NULL,
  `salary_range_max` decimal(15,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('pending','active','rejected') DEFAULT 'pending',
  `rejection_reason` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`job_id`, `startup_id`, `role`, `description`, `requirements`, `location`, `salary_range_min`, `salary_range_max`, `created_at`, `updated_at`, `status`, `rejection_reason`) VALUES
(1, 1, 'Seller', 'eme', 'eme', 'Baguio City', 1000.00, 100000.00, '2025-08-04 22:30:56', '2025-08-04 22:31:25', 'active', NULL);

--
-- Triggers `jobs`
--
DELIMITER $$
CREATE TRIGGER `prevent_zero_job_id` BEFORE INSERT ON `jobs` FOR EACH ROW BEGIN
    IF NEW.job_id = 0 THEN
        SET NEW.job_id = NULL;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `job_seekers`
--

CREATE TABLE `job_seekers` (
  `job_seeker_id` int(11) NOT NULL,
  `skills` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`skills`)),
  `preferred_industries` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`preferred_industries`)),
  `desired_role` varchar(255) DEFAULT NULL,
  `experience_level` enum('entry','mid','senior') NOT NULL,
  `location_preference` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `job_seekers`
--

INSERT INTO `job_seekers` (`job_seeker_id`, `skills`, `preferred_industries`, `desired_role`, `experience_level`, `location_preference`, `bio`, `created_at`, `updated_at`) VALUES
(4, '[\"\"]', NULL, '', 'entry', '', NULL, '2025-08-04 22:28:05', '2025-08-04 22:28:05');

--
-- Triggers `job_seekers`
--
DELIMITER $$
CREATE TRIGGER `prevent_zero_job_seeker_id` BEFORE INSERT ON `job_seekers` FOR EACH ROW BEGIN
    IF NEW.job_seeker_id = 0 THEN
        SET NEW.job_seeker_id = NULL;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `matches`
--

CREATE TABLE `matches` (
  `match_id` int(11) NOT NULL,
  `startup_id` int(11) NOT NULL,
  `investor_id` int(11) NOT NULL,
  `match_score` decimal(5,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `matches`
--

INSERT INTO `matches` (`match_id`, `startup_id`, `investor_id`, `match_score`, `created_at`) VALUES
(1, 1, 3, 0.00, '2025-08-04 22:25:50');

--
-- Triggers `matches`
--
DELIMITER $$
CREATE TRIGGER `prevent_zero_match_id` BEFORE INSERT ON `matches` FOR EACH ROW BEGIN
    IF NEW.match_id = 0 THEN
        SET NEW.match_id = NULL;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `message_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `status` enum('unread','read') DEFAULT 'unread',
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `request_status` enum('pending','approved','rejected') DEFAULT 'pending',
  `is_intro_message` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`message_id`, `sender_id`, `receiver_id`, `content`, `status`, `sent_at`, `request_status`, `is_intro_message`) VALUES
(1, 1, 3, 'hi', 'unread', '2025-08-04 22:43:02', 'approved', 1),
(2, 3, 1, 'hello', 'read', '2025-08-04 22:43:19', 'approved', 0),
(3, 3, 1, 'hii', 'unread', '2025-08-04 22:52:10', 'approved', 0),
(4, 3, 1, 'hii', 'unread', '2025-08-04 22:52:13', 'approved', 0),
(5, 4, 1, 'Hello!', 'unread', '2025-08-06 23:31:18', 'pending', 1);

--
-- Triggers `messages`
--
DELIMITER $$
CREATE TRIGGER `create_conversation_request` AFTER INSERT ON `messages` FOR EACH ROW BEGIN
    IF NEW.is_intro_message = TRUE THEN
        -- Check if a request already exists
        IF NOT EXISTS (
            SELECT 1 FROM Conversation_Requests 
            WHERE sender_id = NEW.sender_id 
            AND receiver_id = NEW.receiver_id
        ) THEN
            INSERT INTO Conversation_Requests (sender_id, receiver_id)
            VALUES (NEW.sender_id, NEW.receiver_id);
        END IF;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `prevent_zero_message_id` BEFORE INSERT ON `messages` FOR EACH ROW BEGIN
    IF NEW.message_id = 0 THEN
        SET NEW.message_id = NULL;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `message_files`
--

CREATE TABLE `message_files` (
  `file_id` int(11) NOT NULL,
  `message_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `type` enum('message','application_status','investment_match','job_offer','system_alert','startup_status') NOT NULL,
  `message` text NOT NULL,
  `status` enum('unread','read') DEFAULT 'unread',
  `job_id` int(11) DEFAULT NULL,
  `application_id` int(11) DEFAULT NULL,
  `match_id` int(11) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `user_id`, `sender_id`, `type`, `message`, `status`, `job_id`, `application_id`, `match_id`, `url`, `created_at`) VALUES
(1, 1, 2, 'system_alert', 'Your startup has been approved by the admin.', 'unread', NULL, NULL, NULL, NULL, '2025-08-04 22:18:14'),
(2, 1, NULL, 'startup_status', 'Your startup Kapital has been updated to: approved', 'unread', NULL, NULL, NULL, NULL, '2025-08-04 22:18:14'),
(3, 3, 2, 'system_alert', 'Your verification document has been approved. Your account is now verified.', 'read', NULL, NULL, NULL, NULL, '2025-08-04 22:18:28'),
(4, 1, 3, 'investment_match', 'Your startup has been matched with an investor!', 'read', NULL, NULL, 1, 'match_details.php?match_id=1', '2025-08-04 22:25:50'),
(5, 3, NULL, 'investment_match', 'You have successfully matched with the startup: Kapital', 'read', NULL, NULL, 1, NULL, '2025-08-04 22:25:50'),
(6, 4, 2, 'system_alert', 'Your verification document has been approved. Your account is now verified.', 'read', NULL, NULL, NULL, NULL, '2025-08-04 23:30:30'),
(7, 1, 4, 'application_status', 'Job seeker JOB SEEKER applied for your job: Seller', 'read', 1, 1, NULL, NULL, '2025-08-04 23:31:30'),
(8, 4, 1, 'application_status', 'The status of your application for job: Seller has been updated to: interviewed', 'read', NULL, 1, NULL, NULL, '2025-08-04 23:33:28'),
(9, 4, 1, 'application_status', 'Your application for the Seller role has been updated to interviewed.', 'unread', 1, 1, NULL, NULL, '2025-08-04 23:33:28'),
(10, 5, 2, 'system_alert', 'Your verification document has been approved. Your account is now verified.', 'unread', NULL, NULL, NULL, NULL, '2025-08-06 23:24:11');

--
-- Triggers `notifications`
--
DELIMITER $$
CREATE TRIGGER `prevent_zero_notification_id` BEFORE INSERT ON `notifications` FOR EACH ROW BEGIN
    IF NEW.notification_id = 0 THEN
        SET NEW.notification_id = NULL;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `resumes`
--

CREATE TABLE `resumes` (
  `resume_id` int(11) NOT NULL,
  `job_seeker_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` varchar(50) NOT NULL,
  `file_size` int(11) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Triggers `resumes`
--
DELIMITER $$
CREATE TRIGGER `prevent_zero_resume_id` BEFORE INSERT ON `resumes` FOR EACH ROW BEGIN
    IF NEW.resume_id = 0 THEN
        SET NEW.resume_id = NULL;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `startups`
--

CREATE TABLE `startups` (
  `startup_id` int(11) NOT NULL,
  `entrepreneur_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `industry` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `funding_needed` decimal(15,2) DEFAULT NULL,
  `pitch_deck_url` varchar(255) DEFAULT NULL,
  `business_plan_url` varchar(255) DEFAULT NULL,
  `approval_status` enum('pending','approved','rejected') DEFAULT 'pending',
  `approved_by` int(11) DEFAULT NULL,
  `approval_comment` text DEFAULT NULL,
  `logo_url` varchar(255) DEFAULT NULL,
  `video_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `funding_stage` enum('startup','seed','series_a','series_b','series_c','exit') NOT NULL,
  `website` varchar(255) DEFAULT NULL,
  `startup_stage` enum('ideation','validation','mvp','growth','maturity') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `startups`
--

INSERT INTO `startups` (`startup_id`, `entrepreneur_id`, `name`, `industry`, `description`, `location`, `funding_needed`, `pitch_deck_url`, `business_plan_url`, `approval_status`, `approved_by`, `approval_comment`, `logo_url`, `video_url`, `created_at`, `updated_at`, `funding_stage`, `website`, `startup_stage`) VALUES
(1, 1, 'Kapital', 'Digital Marketing', 'KAPITAL is a centralized digital platform designed to empower Cordilleran entrepreneurs, investors, and job seekers by bridging opportunity gaps in the local innovation and employment ecosystem. It serves as a dynamic hub where startups can showcase their ventures, investors can discover promising projects, and job seekers can access relevant opportunities—all within a single, streamlined interface.', 'Baguio City', NULL, NULL, NULL, 'approved', 2, '', 'uploads/logos/689130ed04ef6_KAPITAL_Favicon__white_-removebg-preview.png', NULL, '2025-08-04 22:15:09', '2025-08-04 22:18:14', '', 'https://kapital-taraki.org', 'mvp');

--
-- Triggers `startups`
--
DELIMITER $$
CREATE TRIGGER `prevent_zero_startup_id` BEFORE INSERT ON `startups` FOR EACH ROW BEGIN
    IF NEW.startup_id = 0 THEN
        SET NEW.startup_id = NULL;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `startup_status_update_notification` AFTER UPDATE ON `startups` FOR EACH ROW BEGIN
    IF OLD.approval_status != NEW.approval_status THEN
        INSERT INTO Notifications (user_id, sender_id, type, message, status)
        VALUES (
            (SELECT entrepreneur_id FROM Startups WHERE startup_id = NEW.startup_id),
            NULL,
            'startup_status',
            CONCAT('Your startup ', NEW.name, ' has been updated to: ', NEW.approval_status),
            'unread'
        );
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `startup_profiles`
--

CREATE TABLE `startup_profiles` (
  `startup_profile_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `startup_name` varchar(255) NOT NULL,
  `founding_date` date DEFAULT NULL,
  `team_size` int(11) DEFAULT NULL,
  `funding_stage` varchar(50) DEFAULT NULL,
  `pitch_deck_url` varchar(255) DEFAULT NULL,
  `business_plan_url` varchar(255) DEFAULT NULL,
  `website_url` varchar(255) DEFAULT NULL,
  `development_stage` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Triggers `startup_profiles`
--
DELIMITER $$
CREATE TRIGGER `prevent_zero_startup_profile_id` BEFORE INSERT ON `startup_profiles` FOR EACH ROW BEGIN
    IF NEW.startup_profile_id = 0 THEN
        SET NEW.startup_profile_id = NULL;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

CREATE TABLE `tickets` (
  `ticket_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `type` enum('bug','suggestion','other') NOT NULL,
  `status` enum('open','in-progress','resolved','closed') NOT NULL DEFAULT 'open',
  `admin_notes` text DEFAULT NULL,
  `admin_response` text DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tickets`
--

INSERT INTO `tickets` (`ticket_id`, `user_id`, `title`, `description`, `type`, `status`, `admin_notes`, `admin_response`, `created_at`, `updated_at`) VALUES
(1, 3, 'eme', 'eme', 'bug', '', '', NULL, '2025-08-05 07:24:46', '2025-08-05 07:25:13');

--
-- Triggers `tickets`
--
DELIMITER $$
CREATE TRIGGER `prevent_zero_ticket_id` BEFORE INSERT ON `tickets` FOR EACH ROW BEGIN
    IF NEW.ticket_id = 0 THEN
        SET NEW.ticket_id = NULL;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('entrepreneur','investor','job_seeker','admin','startup') NOT NULL,
  `verification_status` enum('pending','verified','not approved') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `profile_picture_url` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `introduction` text DEFAULT NULL,
  `accomplishments` text DEFAULT NULL,
  `education` text DEFAULT NULL,
  `employment` text DEFAULT NULL,
  `gender` enum('male','female','other','prefer_not_to_say') DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `public_email` varchar(255) DEFAULT NULL,
  `industry` varchar(255) DEFAULT NULL,
  `show_in_search` tinyint(1) DEFAULT 1,
  `show_in_messages` tinyint(1) DEFAULT 1,
  `show_in_pages` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `role`, `verification_status`, `created_at`, `updated_at`, `profile_picture_url`, `location`, `introduction`, `accomplishments`, `education`, `employment`, `gender`, `birthdate`, `contact_number`, `public_email`, `industry`, `show_in_search`, `show_in_messages`, `show_in_pages`) VALUES
(1, 'Jester Perez', 'jes.ayson.perez@gmail.com', '$2y$10$jEoL2YQyElnqwsIy9xKo6ec5NRWNiYrWpgT6pCKCTzIQo39hWh1mS', 'entrepreneur', 'verified', '2025-08-04 21:39:54', '2025-08-04 22:09:52', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, 1),
(2, 'ADMIN', 'ADMINDemo@gmail.com', '$2y$10$Kvfu/5nemgxsC3WSXr48LeRQ5d/ydQEWv3jYFG.QdImPQKt6XIZXK', 'admin', 'pending', '2025-08-04 22:07:08', '2025-08-04 22:07:22', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, 1),
(3, 'INVESTOR', 'INVESTdemo@gmail.com', '$2y$10$QSFjQkaO1g3vflaExehIwe5dtrqs93uc0m31MbIITPxoB4VgMUX7G', 'investor', 'verified', '2025-08-04 22:16:15', '2025-08-04 22:18:28', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, 1),
(4, 'JOB SEEKER', 'JOBdemo@gmail.com', '$2y$10$1bttvPP/HJKu0QWeKM.KMOLILlIM6q49dga04WnM4tf9pN5n/mMqK', 'job_seeker', 'verified', '2025-08-04 22:28:05', '2025-08-04 23:30:30', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, 1),
(5, 'Test', 'test@gmail.com', '$2y$10$/0Zi4Ftt2ii8W8ze.xZHz.AQX2EMqLrfn408fElBywy1xRW4lPWVu', 'entrepreneur', 'verified', '2025-08-06 23:18:36', '2025-08-06 23:24:11', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, 1);

--
-- Triggers `users`
--
DELIMITER $$
CREATE TRIGGER `create_user_social_links` AFTER INSERT ON `users` FOR EACH ROW BEGIN
    INSERT INTO User_Social_Links (user_id)
    VALUES (NEW.user_id);
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `prevent_zero_user_id` BEFORE INSERT ON `users` FOR EACH ROW BEGIN
    IF NEW.user_id = 0 THEN
        SET NEW.user_id = NULL;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Stand-in structure for view `uservisiblemessages`
-- (See below for the actual view)
--
CREATE TABLE `uservisiblemessages` (
`message_id` int(11)
,`sender_id` int(11)
,`receiver_id` int(11)
,`content` text
,`status` enum('unread','read')
,`sent_at` timestamp
,`request_status` enum('pending','approved','rejected')
,`is_intro_message` tinyint(1)
);

-- --------------------------------------------------------

--
-- Table structure for table `user_conversations`
--

CREATE TABLE `user_conversations` (
  `user_id` int(11) NOT NULL,
  `other_user_id` int(11) NOT NULL,
  `muted` tinyint(1) DEFAULT 0,
  `archived` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_conversations`
--

INSERT INTO `user_conversations` (`user_id`, `other_user_id`, `muted`, `archived`) VALUES
(3, 1, 1, 1),
(3, 1, 0, 1),
(3, 1, 0, 1),
(3, 1, 0, 1),
(3, 1, 0, 1),
(3, 1, 1, 1),
(3, 1, 1, 1),
(1, 3, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_social_links`
--

CREATE TABLE `user_social_links` (
  `user_id` int(11) NOT NULL,
  `facebook_url` varchar(255) DEFAULT NULL,
  `twitter_url` varchar(255) DEFAULT NULL,
  `instagram_url` varchar(255) DEFAULT NULL,
  `linkedin_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_social_links`
--

INSERT INTO `user_social_links` (`user_id`, `facebook_url`, `twitter_url`, `instagram_url`, `linkedin_url`, `created_at`, `updated_at`) VALUES
(1, NULL, NULL, NULL, NULL, '2025-08-04 21:39:54', '2025-08-04 21:39:54'),
(2, NULL, NULL, NULL, NULL, '2025-08-04 22:07:08', '2025-08-04 22:07:08'),
(3, NULL, NULL, NULL, NULL, '2025-08-04 22:16:15', '2025-08-04 22:16:15'),
(4, NULL, NULL, NULL, NULL, '2025-08-04 22:28:05', '2025-08-04 22:28:05'),
(5, NULL, NULL, NULL, NULL, '2025-08-06 23:18:36', '2025-08-06 23:18:36');

-- --------------------------------------------------------

--
-- Table structure for table `user_token_usage`
--

CREATE TABLE `user_token_usage` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token_count` int(11) NOT NULL,
  `usage_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_token_usage`
--

INSERT INTO `user_token_usage` (`id`, `user_id`, `token_count`, `usage_date`, `created_at`) VALUES
(1, 1, 366, '2025-08-07', '2025-08-07 00:05:19'),
(2, 1, 728, '2025-08-07', '2025-08-07 00:11:35');

--
-- Triggers `user_token_usage`
--
DELIMITER $$
CREATE TRIGGER `prevent_zero_token_usage_id` BEFORE INSERT ON `user_token_usage` FOR EACH ROW BEGIN
    IF NEW.id = 0 THEN
        SET NEW.id = NULL;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `valid_document_types`
--

CREATE TABLE `valid_document_types` (
  `id` int(11) NOT NULL,
  `type_code` varchar(50) NOT NULL,
  `type_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `verification_documents`
--

CREATE TABLE `verification_documents` (
  `document_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `document_type` enum('government_id','passport','drivers_license','business_registration','professional_license','tax_certificate','bank_statement','utility_bill','proof_of_address','employment_certificate','educational_certificate','other') NOT NULL,
  `document_number` varchar(100) DEFAULT NULL,
  `issue_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `issuing_authority` varchar(100) DEFAULT NULL,
  `additional_info` text DEFAULT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` varchar(50) NOT NULL,
  `file_size` int(11) NOT NULL,
  `status` enum('pending','approved','not approved') DEFAULT 'pending',
  `rejection_reason` text DEFAULT NULL,
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `verification_documents`
--

INSERT INTO `verification_documents` (`document_id`, `user_id`, `document_type`, `document_number`, `issue_date`, `expiry_date`, `issuing_authority`, `additional_info`, `file_name`, `file_path`, `file_type`, `file_size`, `status`, `rejection_reason`, `reviewed_by`, `reviewed_at`, `uploaded_at`) VALUES
(1, 1, 'drivers_license', '123', '2003-12-08', '2003-12-08', '', NULL, 'doc_1_1754345181.jpg', 'uploads/verification_documents/doc_1_1754345181.jpg', 'image/jpeg', 432179, 'approved', NULL, 2, '2025-08-04 22:09:52', '2025-08-04 22:06:21'),
(2, 3, 'government_id', 'eme', '0230-12-08', '0000-00-00', '', NULL, 'doc_3_1754345871.jpg', 'uploads/verification_documents/doc_3_1754345871.jpg', 'image/jpeg', 132545, 'approved', NULL, 2, '2025-08-04 22:18:28', '2025-08-04 22:17:51'),
(3, 4, 'government_id', 'eme', '0003-02-01', '0008-09-21', 'eme', NULL, 'doc_4_1754350176.pdf', 'uploads/verification_documents/doc_4_1754350176.pdf', 'application/pdf', 2672983, 'approved', NULL, 2, '2025-08-04 23:30:30', '2025-08-04 23:29:36'),
(4, 5, 'employment_certificate', 'a', '2025-12-08', '2030-12-08', 'a', NULL, 'doc_5_1754522529.jpg', 'uploads/verification_documents/doc_5_1754522529.jpg', 'image/jpeg', 49051, 'approved', NULL, 2, '2025-08-06 23:24:11', '2025-08-06 23:22:09');

--
-- Triggers `verification_documents`
--
DELIMITER $$
CREATE TRIGGER `prevent_zero_document_id` BEFORE INSERT ON `verification_documents` FOR EACH ROW BEGIN
    IF NEW.document_id = 0 THEN
        SET NEW.document_id = NULL;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `track_document_status_changes` AFTER UPDATE ON `verification_documents` FOR EACH ROW BEGIN
    IF OLD.status != NEW.status THEN
        INSERT INTO Document_Verification_History (
            document_id,
            previous_status,
            new_status,
            changed_by,
            change_reason
        ) VALUES (
            NEW.document_id,
            OLD.status,
            NEW.status,
            NEW.reviewed_by,
            NEW.rejection_reason
        );
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Structure for view `uservisiblemessages`
--
DROP TABLE IF EXISTS `uservisiblemessages`;

CREATE ALGORITHM=UNDEFINED DEFINER=`u882993081_tarakikapital`@`127.0.0.1` SQL SECURITY DEFINER VIEW `uservisiblemessages`  AS SELECT `m`.`message_id` AS `message_id`, `m`.`sender_id` AS `sender_id`, `m`.`receiver_id` AS `receiver_id`, `m`.`content` AS `content`, `m`.`status` AS `status`, `m`.`sent_at` AS `sent_at`, `m`.`request_status` AS `request_status`, `m`.`is_intro_message` AS `is_intro_message` FROM (`messages` `m` left join `conversation_requests` `cr` on(`m`.`sender_id` = `cr`.`sender_id` and `m`.`receiver_id` = `cr`.`receiver_id`)) WHERE `m`.`request_status` = 'approved' OR `m`.`is_intro_message` = 1 AND `m`.`request_status` = 'pending' OR `m`.`sender_id` = `m`.`receiver_id` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ai_conversations`
--
ALTER TABLE `ai_conversations`
  ADD PRIMARY KEY (`conversation_id`);

--
-- Indexes for table `ai_response_cache`
--
ALTER TABLE `ai_response_cache`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`application_id`);

--
-- Indexes for table `conversation_requests`
--
ALTER TABLE `conversation_requests`
  ADD PRIMARY KEY (`request_id`);

--
-- Indexes for table `document_verification_history`
--
ALTER TABLE `document_verification_history`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`job_id`);

--
-- Indexes for table `matches`
--
ALTER TABLE `matches`
  ADD PRIMARY KEY (`match_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`);

--
-- Indexes for table `resumes`
--
ALTER TABLE `resumes`
  ADD PRIMARY KEY (`resume_id`);

--
-- Indexes for table `startups`
--
ALTER TABLE `startups`
  ADD PRIMARY KEY (`startup_id`);

--
-- Indexes for table `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`ticket_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `user_token_usage`
--
ALTER TABLE `user_token_usage`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `verification_documents`
--
ALTER TABLE `verification_documents`
  ADD PRIMARY KEY (`document_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ai_conversations`
--
ALTER TABLE `ai_conversations`
  MODIFY `conversation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `ai_response_cache`
--
ALTER TABLE `ai_response_cache`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `application_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `conversation_requests`
--
ALTER TABLE `conversation_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `document_verification_history`
--
ALTER TABLE `document_verification_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `job_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `matches`
--
ALTER TABLE `matches`
  MODIFY `match_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `resumes`
--
ALTER TABLE `resumes`
  MODIFY `resume_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `startups`
--
ALTER TABLE `startups`
  MODIFY `startup_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tickets`
--
ALTER TABLE `tickets`
  MODIFY `ticket_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `user_token_usage`
--
ALTER TABLE `user_token_usage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `verification_documents`
--
ALTER TABLE `verification_documents`
  MODIFY `document_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
