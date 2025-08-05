import sys
import json
import numpy as np
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import cosine_similarity
from sklearn.preprocessing import MultiLabelBinarizer

def preprocess_industry(industry):
    """Preprocess industry text for better matching"""
    return industry.lower().strip()

def calculate_industry_similarity(user_industry, startup_industries):
    """Calculate similarity between user's industry and startup industries"""
    # Create TF-IDF vectorizer
    vectorizer = TfidfVectorizer(preprocessor=preprocess_industry)
    
    # Combine user industry with startup industries for vectorization
    all_industries = [user_industry] + startup_industries
    tfidf_matrix = vectorizer.fit_transform(all_industries)
    
    # Calculate similarity between user industry and each startup industry
    similarities = cosine_similarity(tfidf_matrix[0:1], tfidf_matrix[1:])
    return similarities[0]

def calculate_location_similarity(user_location, startup_location):
    """Calculate similarity between user's location and startup location"""
    if not user_location or not startup_location:
        return 0.5  # Neutral score if location is missing
    
    user_location = user_location.lower().strip()
    startup_location = startup_location.lower().strip()
    
    # Exact match
    if user_location == startup_location:
        return 1.0
    
    # Check if one location contains the other
    if user_location in startup_location or startup_location in user_location:
        return 0.8
    
    return 0.0

def calculate_match_score(user_data, startup_data):
    """Calculate overall match score between user and startup"""
    # Calculate individual similarity scores
    industry_similarity = calculate_industry_similarity(
        user_data['industry'],
        [startup_data['industry']]
    )[0]
    
    location_similarity = calculate_location_similarity(
        user_data.get('location', ''),
        startup_data.get('location', '')
    )
    
    # Weight the different factors
    weights = {
        'industry': 0.7,  # Increased weight since we removed funding stage
        'location': 0.3
    }
    
    # Calculate weighted score
    match_score = (
        weights['industry'] * industry_similarity +
        weights['location'] * location_similarity
    )
    
    return {
        'match_score': float(match_score),
        'details': {
            'industry_similarity': float(industry_similarity),
            'location_similarity': float(location_similarity)
        }
    }

def main():
    # Load data from PHP
    data = json.loads(sys.argv[1])
    user = data['user']
    startups = data['startups']
    
    # Calculate match scores for each startup
    matches = []
    for startup in startups:
        match_result = calculate_match_score(user, startup)
        matches.append({
            'startup_id': startup['startup_id'],
            'name': startup['name'],
            'score': match_result['match_score'],
            'details': match_result['details']
        })
    
    # Sort matches by score
    matches.sort(key=lambda x: x['score'], reverse=True)
    
    # Return results as JSON
    print(json.dumps(matches))

if __name__ == "__main__":
    main() 