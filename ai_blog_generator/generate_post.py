import requests
import mysql.connector
import re
import random
import time
from datetime import datetime
import markdown

OLLAMA_URL = "http://localhost:11434/api/generate"

DB_CONFIG = {
    "host": "localhost",
    "port": 3306,
    "user": "root",
    "password": "YourDBPassword",
    "database": "blog_ai"
}

# ----------------------------
# LOGGING (COLORS REMOVED)
# ----------------------------
def log(text):
    """Simple logging without ANSI color codes."""
    print(text)

# ----------------------------
# CALL MODEL (WITH TIMING)
# ----------------------------
def call_model(model, prompt):
    log(f"[MODEL] Using: {model}")
    start = time.time()

    try:
        response = requests.post(OLLAMA_URL, json={
            "model": model,
            "prompt": prompt,
            "stream": False
        })

        data = response.json()
        duration = time.time() - start

        if "response" in data:
            # 1. Convert nanoseconds to seconds for all durations
            load_s = data.get("load_duration", 0) / 1_000_000_000
            prompt_s = data.get("prompt_eval_duration", 0) / 1_000_000_000
            eval_s = data.get("eval_duration", 1) / 1_000_000_000
            
            # 2. Extract token counts
            p_tokens = data.get("prompt_eval_count", 0)  # Input tokens
            g_tokens = data.get("eval_count", 0)         # Output tokens
            
            # 3. Calculate speeds
            tps = g_tokens / eval_s if eval_s > 0 else 0
            
            # Display detailed stats
            log(f"--- Performance Stats ---")
            log(f"Total Request Time: {duration:.2f}s")
            log(f"Model Load Time:    {load_s:.2f}s")
            log(f"Input/Prompt Size:  {p_tokens} tokens")
            log(f"Output/Generation:  {g_tokens} tokens")
            log(f"Generation Speed:   {tps:.2f} tokens/s")
            log(f"-------------------------")
            
            return data["response"]

        if "error" in data:
            log(f"[ERROR] Model error: {data['error']}")
            return ""

        return ""

    except Exception as e:
        log(f"[FATAL] Request failed: {e}")
        return ""

# ----------------------------
# STORY IDEA (ENGLISH)
# ----------------------------
def generate_topic():
    model = "llama3.2:1b"
    styles = [
        "psychological horror",
        "supernatural horror",
        "small-town horror",
        "isolation horror",
        "paranoia-driven horror"
    ]
    style = random.choice(styles)

    prompt = f"""
Generate ONE short horror story idea.

Style:
- {style}
- Subtle and unsettling

Rules:
- One sentence only
- No lists
- No explanations
- English only

Example:
A man keeps receiving phone calls from himself one day in the future.

Now generate one:
"""
    return call_model(model, prompt).strip()

# ----------------------------
# STORY GENERATION (ENGLISH)
# ----------------------------
def generate_article(topic):
    model = "mistral:7b"
    prompt = f"""
Write a short horror story based on this idea:

{topic}

Style:
- Psychological horror
- Slow tension build
- Realistic dialogue
- Subtle supernatural or disturbing elements

Rules:
- 800–1200 words
- Start with "# Title"
- Markdown format
- No clichés
- Include a subtle twist ending
- English only

Make it immersive and atmospheric.
"""
    return call_model(model, prompt)

# ----------------------------
# TRANSLATE TO BULGARIAN
# todorov/bggpt:Gemma-3-4B-IT-Q4_K_M <- smaller, faster
# todorov/bggpt:Gemma-3-12B-IT-Q4_K_M
# ----------------------------
def translate_to_bulgarian(article):
    model = "todorov/bggpt:Gemma-3-4B-IT-Q4_K_M"
    prompt = f"""
Translate the following horror story into natural Bulgarian.

IMPORTANT:
- Keep Markdown formatting
- Preserve tone, pacing, and atmosphere
- Make it sound like native Bulgarian literature
- Do NOT shorten or summarize
- Translate EVERYTHING including title
- Output must be fully in Bulgarian

STORY:
{article}
"""
    return call_model(model, prompt)

# ----------------------------
# SEO (ENGLISH ONLY)
# ----------------------------
def generate_seo(article_en):
    model = "llama3.2:1b"
    prompt = f"""
Extract SEO data from this horror story.

Return EXACT format:

TITLE: ...
DESCRIPTION: ...
TAGS: ...

Rules:
- TITLE max 60 characters
- DESCRIPTION max 155 characters
- TAGS must be comma-separated
- English only

Use tags like:
horror, psychological, supernatural, thriller, short story, dark fiction

ARTICLE:
{article_en}
"""
    return call_model(model, prompt)

def parse_seo(text):
    title, desc, tags = "", "", ""
    for line in text.split("\n"):
        clean_line = line.strip().replace("*", "") # Removes **bolding**
        
        if clean_line.upper().startswith("TITLE:"):
            title = clean_line.split(":", 1)[1].strip()
        elif clean_line.upper().startswith("DESCRIPTION:"):
            desc = clean_line.split(":", 1)[1].strip()
        elif clean_line.upper().startswith("TAGS:"):
            tags = clean_line.split(":", 1)[1].strip()
    return title, desc, tags

# ----------------------------
# UTILITIES
# ----------------------------
def extract_title(article):
    match = re.search(r"# (.+)", article)
    return match.group(1).strip() if match else "Untitled Horror Story"

def slugify(title):
    return re.sub(r'[^a-z0-9]+', '-', title.lower()).strip('-')

def markdown_to_html(md):
    html = markdown.markdown(md, extensions=["extra", "nl2br", "sane_lists", "smarty"])
    html = html.replace("<p>", "<p class='lead'>", 1) 
    return html

# ----------------------------
# DB HELPERS
# ----------------------------
def topic_exists(topic):
    conn = mysql.connector.connect(**DB_CONFIG)
    cursor = conn.cursor()
    cursor.execute("SELECT COUNT(*) FROM topics WHERE topic=%s", (topic,))
    exists = cursor.fetchone()[0] > 0
    cursor.close()
    conn.close()
    return exists

def save_topic(topic):
    conn = mysql.connector.connect(**DB_CONFIG)
    cursor = conn.cursor()
    cursor.execute("INSERT INTO topics (topic) VALUES (%s)", (topic,))
    conn.commit()
    cursor.close()
    conn.close()

def save_post(title, slug, html, seo_title, seo_desc, tags):
    conn = mysql.connector.connect(**DB_CONFIG)
    cursor = conn.cursor()
    query = """
    INSERT INTO posts
    (title, slug, content, seo_title, seo_description, tags, status, created_at)
    VALUES (%s, %s, %s, %s, %s, %s, %s, %s)
    """
    cursor.execute(query, (
        title, slug, html, seo_title, seo_desc, tags, "draft", datetime.now()
    ))
    conn.commit()
    cursor.close()
    conn.close()

# ----------------------------
# MAIN PIPELINE
# ----------------------------
if __name__ == "__main__":
    pipeline_start = time.time()

    log("\n--- STORY PIPELINE START ---\n")

    # IDEA
    step_start = time.time()
    log("[STEP] Generating idea...")
    topic = generate_topic()
    log(f"[IDEA]: {topic}")
    log(f"[STEP TIME] {time.time() - step_start:.2f}s\n")

    if topic_exists(topic):
        log("[SKIP] Duplicate idea.")
        exit()

    save_topic(topic)

    # STORY
    step_start = time.time()
    log("[STEP] Generating story (EN)...")
    article_en = generate_article(topic)
    log(f"[STEP TIME] {time.time() - step_start:.2f}s\n")

    # SEO
    step_start = time.time()
    log("[STEP] Generating SEO...")
    seo_raw = generate_seo(article_en)
    seo_title, seo_desc, tags = parse_seo(seo_raw)
    log(f"[STEP TIME] {time.time() - step_start:.2f}s\n")

    # TRANSLATION
    step_start = time.time()
    log("[STEP] Translating to Bulgarian...")
    article_bg = translate_to_bulgarian(article_en)
    log(f"[STEP TIME] {time.time() - step_start:.2f}s\n")

    # FINAL PROCESSING
    title = extract_title(article_en)
    slug = slugify(title)
    html = markdown_to_html(article_bg)

    # SAVE
    step_start = time.time()
    log("[STEP] Saving...")
    save_post(title, slug, html, seo_title, seo_desc, tags)
    log(f"[STEP TIME] {time.time() - step_start:.2f}s\n")

    total_time = time.time() - pipeline_start
    log(f"--- DONE in {total_time:.2f}s ---\n")