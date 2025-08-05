<?php
// This file provides direct, hardcoded responses to common entrepreneurial questions
// It can be used as a fallback when the OpenAI API is not working properly

function getDirectResponse($question) {
    // Convert the question to lowercase for easier matching
    $question = strtolower($question);
    
    // Check for common questions and provide direct responses
    if (strpos($question, 'attract') !== false && strpos($question, 'investor') !== false) {
        return "To attract potential investors, follow these key strategies:

1. Create a compelling pitch deck that clearly communicates:
   - Your unique value proposition
   - Market opportunity and size
   - Business model and revenue streams
   - Competitive advantage
   - Traction and metrics
   - Team and expertise
   - Financial projections and funding needs

2. Build relationships with investors:
   - Attend industry events, conferences, and pitch competitions
   - Leverage warm introductions through your network
   - Engage with investors on social media and professional platforms
   - Follow up consistently but don't be pushy

3. Demonstrate traction and growth:
   - Show consistent user/customer growth
   - Highlight key partnerships and collaborations
   - Present clear metrics that prove market demand
   - Demonstrate product-market fit with testimonials

4. Prepare for due diligence:
   - Have detailed financial projections ready
   - Organize legal documents and intellectual property
   - Be prepared to discuss risks and mitigation strategies
   - Have a clear use of funds plan

5. Target the right investors:
   - Research investors who focus on your industry and stage
   - Understand their investment criteria and portfolio
   - Tailor your pitch to their specific interests
   - Consider both angel investors and venture capital firms

Remember that attracting investors is a process that requires persistence, preparation, and building genuine relationships over time.";
    }
    
    if (strpos($question, 'financial') !== false && strpos($question, 'metric') !== false) {
        return "Key financial metrics to track for your startup:

1. Revenue Metrics:
   - Monthly Recurring Revenue (MRR)
   - Annual Recurring Revenue (ARR)
   - Revenue Growth Rate
   - Average Revenue Per User (ARPU)
   - Customer Lifetime Value (CLV)

2. Customer Metrics:
   - Customer Acquisition Cost (CAC)
   - Customer Churn Rate
   - Net Promoter Score (NPS)
   - Customer Retention Rate
   - Active Users (Daily/Monthly)

3. Operational Metrics:
   - Gross Margin
   - Operating Margin
   - Burn Rate
   - Runway (months of cash remaining)
   - Unit Economics

4. Marketing Metrics:
   - Cost Per Acquisition (CPA)
   - Conversion Rate
   - Marketing Qualified Leads (MQL)
   - Sales Qualified Leads (SQL)
   - Return on Marketing Investment (ROMI)

5. Product Metrics:
   - Feature Usage
   - User Engagement
   - Time to Value
   - Net Revenue Retention
   - Product-Market Fit Score

For early-stage startups, focus on metrics that validate your business model and show traction. As you grow, shift to metrics that demonstrate scalability and profitability potential.";
    }
    
    if (strpos($question, 'business') !== false && strpos($question, 'plan') !== false) {
        return "To create a compelling business plan, include these essential components:

1. Executive Summary:
   - Concise overview of your business concept
   - Problem you're solving
   - Your solution and unique value proposition
   - Target market and opportunity
   - Business model and revenue streams
   - Team and expertise
   - Financial highlights and funding needs

2. Company Description:
   - Mission and vision statements
   - Company history and background
   - Legal structure and ownership
   - Location and facilities
   - Key milestones achieved

3. Market Analysis:
   - Industry overview and trends
   - Target market segmentation
   - Market size and growth potential
   - Competitive landscape
   - Market entry strategy
   - Regulatory environment

4. Product/Service Line:
   - Detailed description of offerings
   - Product development roadmap
   - Intellectual property
   - Research and development
   - Product lifecycle

5. Marketing and Sales Strategy:
   - Marketing channels and tactics
   - Sales process and methodology
   - Pricing strategy
   - Distribution channels
   - Customer acquisition strategy
   - Brand positioning

6. Operations Plan:
   - Day-to-day operations
   - Supply chain and logistics
   - Technology infrastructure
   - Quality control measures
   - Scalability plans

7. Management Team:
   - Key personnel and roles
   - Organizational structure
   - Hiring plans
   - Advisory board
   - Compensation structure

8. Financial Projections:
   - Income statement projections
   - Cash flow projections
   - Balance sheet projections
   - Break-even analysis
   - Funding requirements
   - Use of funds
   - Exit strategy

Keep your business plan concise (15-25 pages), data-driven, and focused on demonstrating the viability and growth potential of your venture.";
    }
    
    if (strpos($question, 'market') !== false && strpos($question, 'research') !== false) {
        return "Effective market research strategies for startups:

1. Define Your Research Objectives:
   - Identify specific questions you need answered
   - Determine what data will help validate your business concept
   - Set clear goals for your research efforts

2. Primary Research Methods:
   - Customer interviews (aim for 20-30 in-depth conversations)
   - Surveys (use tools like SurveyMonkey or Google Forms)
   - Focus groups
   - User testing and feedback sessions
   - Field observations and ethnographic research
   - Landing page tests with different value propositions

3. Secondary Research Sources:
   - Industry reports and market studies
   - Government databases and statistics
   - Academic research and publications
   - Competitor websites, press releases, and annual reports
   - Social media and online forums
   - Trade associations and professional organizations

4. Competitor Analysis:
   - Direct competitors (similar products/services)
   - Indirect competitors (alternative solutions)
   - Potential future competitors
   - Competitor strengths and weaknesses
   - Pricing strategies and business models
   - Market positioning and branding

5. Customer Segmentation:
   - Demographic analysis
   - Psychographic profiling
   - Behavioral patterns
   - Needs and pain points
   - Willingness to pay
   - Decision-making factors

6. Data Analysis:
   - Quantitative analysis of survey results
   - Qualitative analysis of interview insights
   - Trend identification
   - Pattern recognition
   - Insight generation

7. Validation Techniques:
   - Minimum Viable Product (MVP) testing
   - A/B testing of features or messaging
   - Pre-sales or crowdfunding campaigns
   - Pilot programs with potential customers
   - Expert reviews and feedback

Remember that market research is an ongoing process, not a one-time activity. Continuously gather and analyze data to refine your business strategy and stay responsive to market changes.";
    }
    
    if (strpos($question, 'target') !== false && strpos($question, 'market') !== false) {
        return "To identify your target market effectively:

1. Start with Problem-Solution Fit:
   - Clearly define the problem your product/service solves
   - Identify who experiences this problem most acutely
   - Understand the consequences of the problem
   - Determine how your solution addresses the problem uniquely

2. Conduct Market Segmentation:
   - Demographic segmentation (age, gender, income, education, etc.)
   - Geographic segmentation (location, region, country, etc.)
   - Psychographic segmentation (values, interests, lifestyle, etc.)
   - Behavioral segmentation (usage patterns, brand loyalty, etc.)
   - Firmographic segmentation (for B2B: company size, industry, etc.)

3. Prioritize Your Segments:
   - Evaluate market size and growth potential
   - Assess competition and market saturation
   - Consider your resources and capabilities
   - Analyze customer acquisition costs
   - Evaluate potential profitability

4. Create Detailed Customer Personas:
   - Develop 3-5 detailed personas representing your ideal customers
   - Include demographic information, goals, challenges, and behaviors
   - Add quotes and specific pain points
   - Include buying motivations and decision criteria
   - Visualize your personas with images and descriptions

5. Validate Your Target Market:
   - Conduct customer interviews with potential target customers
   - Test your value proposition with different segments
   - Analyze customer feedback and behavior
   - Measure engagement and conversion rates
   - Adjust your targeting based on data and feedback

6. Focus on Early Adopters:
   - Identify characteristics of early adopters in your market
   - Target customers who are most likely to try new solutions
   - Look for customers with urgent needs or problems
   - Find customers with sufficient resources to purchase
   - Seek customers who are influential in their networks

7. Refine Your Targeting Over Time:
   - Start narrow and expand strategically
   - Test different segments with small marketing campaigns
   - Analyze which segments respond best to your messaging
   - Adjust your product/service based on segment feedback
   - Continuously refine your targeting as you learn more

Remember that your target market may evolve as your business grows. Stay flexible and be willing to adjust your targeting based on real customer data and feedback.";
    }
    
    // Default response for unrecognized questions
    return "I'm your startup advisor. To provide you with the most helpful information, could you please ask a specific question about business planning, market analysis, funding strategies, or startup growth? I'm here to offer detailed, practical advice for entrepreneurs.";
}

// Example usage:
// $response = getDirectResponse("How can I attract potential investors?");
// echo $response;
?> 