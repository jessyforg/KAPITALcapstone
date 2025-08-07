<?php
require_once 'db_connection.php';
require_once 'token_tracker.php';
require_once 'config.php';

class AIServiceMock {
    private $conn;
    private $token_tracker;
    
    public function __construct($conn) {
        $this->conn = $conn;
        $this->token_tracker = new TokenTracker($conn);
        custom_log("Mock AI Service initialized");
    }
    
    public function getAIResponse($user_id, $question) {
        custom_log("Starting mock getAIResponse for user_id: $user_id");
        custom_log("Question: " . substr($question, 0, 100) . "...");
        
        try {
            // Reconnect to database before preparing statement
            custom_log("Reconnecting to database");
            $this->conn = reconnect_db();
            
            // Store the question in the database
            custom_log("Storing question in database");
            $stmt = $this->conn->prepare("
                INSERT INTO ai_conversations (user_id, question, response, created_at) 
                VALUES (?, ?, '', NOW())
            ");
            
            if (!$stmt) {
                custom_log("Failed to prepare statement: " . $this->conn->error, "ERROR");
                return "I apologize, but I'm having trouble connecting to the database. Please try again later.";
            }
            
            $stmt->bind_param("is", $user_id, $question);
            if (!$stmt->execute()) {
                custom_log("Failed to execute statement: " . $stmt->error, "ERROR");
                return "I apologize, but I'm having trouble saving your question. Please try again later.";
            }
            
            $conversation_id = $stmt->insert_id;
            custom_log("Stored question with conversation_id: $conversation_id");
            
            // Generate mock response based on question content
            $response = $this->generateMockResponse($question);
            custom_log("Generated mock response");
            
            // Record token usage (rough estimate)
            $token_count = strlen($question . $response) / 4;
            $this->token_tracker->recordTokenUsage($user_id, $token_count);
            
            // Reconnect to database before updating response
            custom_log("Reconnecting to database for response update");
            $this->conn = reconnect_db();
            
            // Update the response in the database
            custom_log("Updating response in database");
            $stmt = $this->conn->prepare("
                UPDATE ai_conversations 
                SET response = ?, responded_at = NOW()
                WHERE conversation_id = ?
            ");
            
            if (!$stmt) {
                custom_log("Failed to prepare update statement: " . $this->conn->error, "ERROR");
                return $response;
            }
            
            $stmt->bind_param("si", $response, $conversation_id);
            if (!$stmt->execute()) {
                custom_log("Failed to update response: " . $stmt->error, "ERROR");
            } else {
                custom_log("Successfully updated response in database");
            }
            
            custom_log("Returning mock response to user");
            return $response;
        } catch (Exception $e) {
            custom_log("Error in mock getAIResponse: " . $e->getMessage(), "ERROR");
            return "I apologize, but I'm experiencing technical difficulties. Please try again later.";
        }
    }
    
    private function generateMockResponse($question) {
        $question_lower = strtolower($question);
        
        // Generate responses based on keywords in the question
        if (strpos($question_lower, 'business plan') !== false || strpos($question_lower, 'plan') !== false) {
            return "## Creating a Compelling Business Plan

A strong business plan is essential for startup success. Here's a comprehensive approach:

### 1. Executive Summary
- Clearly state your business concept in 2-3 sentences
- Highlight your unique value proposition
- Include key financial projections and funding requirements

### 2. Market Analysis
- Research your target market size and growth potential
- Analyze competitors and identify market gaps
- Define your target customer demographics and behavior

### 3. Business Model
- Explain how you'll generate revenue
- Outline your pricing strategy
- Detail your cost structure and profit margins

### 4. Marketing Strategy
- Define your brand positioning
- Choose appropriate marketing channels (digital, traditional, partnerships)
- Set measurable marketing goals and KPIs

### 5. Financial Projections
- Create 3-year revenue and expense forecasts
- Include cash flow projections
- Calculate break-even point and return on investment

**Pro Tip:** Keep your business plan concise but comprehensive. Investors typically spend only 3-4 minutes on initial reviews, so make every section count!";
        }
        
        if (strpos($question_lower, 'market research') !== false || strpos($question_lower, 'market') !== false) {
            return "## Effective Market Research Strategies

Understanding your market is crucial for startup success. Here are proven research methods:

### 1. Primary Research Methods
- **Surveys and Questionnaires**: Use tools like Google Forms or SurveyMonkey
- **Customer Interviews**: Conduct 1-on-1 conversations with potential customers
- **Focus Groups**: Gather 6-8 people for structured discussions
- **Observation Studies**: Watch how customers behave in natural settings

### 2. Secondary Research Sources
- **Industry Reports**: Use IBISWorld, Statista, or government databases
- **Competitor Analysis**: Study competitors' websites, pricing, and customer reviews
- **Social Media Monitoring**: Track mentions and conversations about your industry
- **Academic Research**: Access studies from universities and research institutions

### 3. Digital Tools for Market Research
- **Google Trends**: Analyze search patterns and seasonal trends
- **Social Media Analytics**: Use native tools or third-party platforms
- **SEO Tools**: Analyze keyword search volumes and competition
- **Customer Review Mining**: Extract insights from review platforms

### 4. Key Metrics to Track
- Market size (TAM, SAM, SOM)
- Customer acquisition cost (CAC)
- Customer lifetime value (CLV)
- Market growth rate and trends

**Remember:** Combine multiple research methods for the most accurate picture of your market!";
        }
        
        if (strpos($question_lower, 'investor') !== false || strpos($question_lower, 'funding') !== false) {
            return "## Attracting Potential Investors

Securing investment requires preparation, presentation, and persistence. Here's your roadmap:

### 1. Prepare Your Foundation
- **Strong Business Model**: Demonstrate clear revenue streams and scalability
- **Market Validation**: Show evidence of customer demand and market size
- **Competitive Advantage**: Highlight what makes you unique and defensible
- **Financial Projections**: Create realistic, data-backed forecasts

### 2. Create a Compelling Pitch Deck
- **Problem & Solution** (2 slides): Clearly define the problem and your solution
- **Market Opportunity** (1-2 slides): Show market size and growth potential
- **Business Model** (1 slide): Explain how you make money
- **Traction** (1-2 slides): Demonstrate progress and customer validation
- **Financial Projections** (1-2 slides): Show revenue, costs, and funding needs
- **Team** (1 slide): Highlight relevant experience and expertise

### 3. Target the Right Investors
- **Angel Investors**: High-net-worth individuals, often former entrepreneurs
- **Venture Capital**: Professional investment firms with industry expertise
- **Crowdfunding**: Platforms like Kickstarter, Indiegogo, or equity crowdfunding
- **Strategic Investors**: Corporations looking for strategic partnerships

### 4. Build Relationships Before You Need Money
- Attend networking events and startup meetups
- Engage with investors on social media and industry platforms
- Seek warm introductions through mutual connections
- Update potential investors on your progress regularly

**Key Tip:** Investors invest in people as much as ideas. Show passion, coachability, and resilience!";
        }
        
        if (strpos($question_lower, 'financial') !== false || strpos($question_lower, 'metrics') !== false) {
            return "## Key Financial Metrics for Startups

Tracking the right metrics helps you make informed decisions and attract investors:

### 1. Revenue Metrics
- **Monthly Recurring Revenue (MRR)**: Predictable monthly revenue
- **Annual Recurring Revenue (ARR)**: MRR × 12 for subscription businesses
- **Revenue Growth Rate**: Month-over-month and year-over-year growth
- **Average Revenue Per User (ARPU)**: Total revenue ÷ number of customers

### 2. Customer Metrics
- **Customer Acquisition Cost (CAC)**: Total acquisition expenses ÷ new customers
- **Customer Lifetime Value (CLV)**: Average revenue per customer over their lifetime
- **CLV:CAC Ratio**: Should be at least 3:1 for healthy unit economics
- **Churn Rate**: Percentage of customers who stop using your product

### 3. Operational Metrics
- **Gross Margin**: (Revenue - Cost of Goods Sold) ÷ Revenue
- **Operating Margin**: (Operating Income ÷ Revenue) × 100
- **Cash Flow**: Money coming in vs. going out
- **Burn Rate**: How much cash you're spending monthly

### 4. Growth Metrics
- **User Growth Rate**: How quickly your user base is expanding
- **Product-Market Fit Indicators**: Net Promoter Score, retention rates
- **Market Share**: Your position relative to competitors
- **Time to Break-Even**: When revenue equals expenses

### 5. Financial Planning Tools
- **Financial Dashboard**: Real-time tracking of key metrics
- **Cash Flow Forecasting**: Predict future cash needs
- **Scenario Planning**: Model different growth scenarios
- **Investor Reports**: Regular updates for stakeholders

**Pro Tip:** Focus on 5-7 key metrics that directly impact your business model. Too many metrics can be overwhelming!";
        }
        
        if (strpos($question_lower, 'target market') !== false || strpos($question_lower, 'target') !== false) {
            return "## Identifying Your Target Market

Finding the right customers is crucial for startup success. Here's a systematic approach:

### 1. Start with Market Segmentation
- **Demographic**: Age, gender, income, education, occupation
- **Geographic**: Location, climate, urban vs. rural, local regulations
- **Psychographic**: Values, interests, lifestyle, personality traits
- **Behavioral**: Usage patterns, brand loyalty, purchase behavior

### 2. Create Customer Personas
- **Primary Persona**: Your ideal customer with detailed characteristics
- **Secondary Personas**: Other important customer segments
- **Pain Points**: What problems are they trying to solve?
- **Goals and Motivations**: What drives their purchasing decisions?

### 3. Validate Your Target Market
- **Customer Interviews**: Talk to 10-20 potential customers
- **Surveys**: Gather data from a larger sample size
- **MVP Testing**: Create a minimum viable product to test demand
- **Landing Page Tests**: Measure interest through sign-ups or pre-orders

### 4. Analyze Market Size
- **Total Addressable Market (TAM)**: The total market demand
- **Serviceable Addressable Market (SAM)**: The portion you can target
- **Serviceable Obtainable Market (SOM)**: The realistic market share you can capture

### 5. Competitive Analysis
- **Direct Competitors**: Companies offering similar solutions
- **Indirect Competitors**: Alternative solutions to the same problem
- **Market Gaps**: Underserved segments or unmet needs
- **Positioning Opportunities**: How to differentiate yourself

### 6. Refine and Focus
- **Beachhead Market**: Start with a small, specific segment
- **Expansion Strategy**: Plan how to grow to adjacent markets
- **Customer Journey Mapping**: Understand how customers discover and buy
- **Feedback Loops**: Continuously gather and act on customer feedback

**Remember:** It's better to dominate a small market than to get lost in a large one!";
        }
        
        // Default response for other questions
        return "## Thank You for Your Question!

I'm a startup advisor here to help you build and grow your business. While I'd love to provide a detailed response to your specific question, I'm currently running in demo mode with pre-written responses.

### Common Topics I Can Help With:
- **Business Planning**: Creating comprehensive business plans and strategies
- **Market Research**: Understanding your target market and competition
- **Funding Strategies**: Attracting investors and securing capital
- **Financial Planning**: Key metrics and financial management
- **Marketing**: Customer acquisition and brand building
- **Operations**: Scaling your business efficiently

### For a Real AI Experience:
To get personalized, dynamic responses to any startup question, you'll need to configure a valid OpenAI API key.

**Note:** This is a demonstration response. In the full version, I can provide detailed, personalized advice for any startup-related question you have!

Feel free to ask about business plans, market research, investor strategies, or financial planning to see more detailed demo responses.";
    }
}
?> 