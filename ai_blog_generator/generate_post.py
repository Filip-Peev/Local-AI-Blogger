import requests
import mysql.connector
import re
from datetime import datetime
import markdown

OLLAMA_URL = "http://localhost:11434/api/generate"

DB_CONFIG = {
    "host": "localhost",
    "port": 3307,
    "user": "root",
    "password": "YourPassword",
    "database": "blog_ai"
}

# ----------------------------
# CALL MODEL
# ----------------------------
def call_model(model, prompt):
    response = requests.post(OLLAMA_URL, json={
        "model": model,
        "prompt": prompt,
        "stream": False
    })
    return response.json()["response"]

# ----------------------------
# TOPIC GENERATOR (PC HARDWARE FOCUSED)
# ----------------------------
def generate_topic():
    prompt = """
Generate ONE highly technical PC hardware blog topic.

Focus ONLY on:
- CPU architecture
- GPU architecture
- RAM latency and bandwidth
- SSD / NVMe storage
- Motherboard chipsets
- PCIe lanes
- Cooling systems (air / liquid)
- Power delivery (VRM)
- Hardware benchmarking

Rules:
- No explanations
- No lists
- Must be a single specific technical idea
- Return ONLY a single line.

Example:
How CPU cache hierarchy impacts real-world performance
Now generate one topic:
"""

    return call_model("llama3.2:1b", prompt).strip()

# ----------------------------
# ARTICLE GENERATOR (HARDWARE FOCUSED)
# ----------------------------
def generate_article(topic):
    prompt = f"""
Write a deep technical PC hardware article.

Topic: {topic}

Rules:
- 800–1200 words
- Start with "# Title"
- Use Markdown headings
- Focus on hardware engineering concepts
- Explain CPU/GPU/architecture behavior
- NO links
- Include examples and comparisons
"""

    return call_model("llama3.2:3b", prompt)

# ----------------------------
# SEO GENERATION
# ----------------------------
def generate_seo(article):
    prompt = f"""
Extract SEO data from this PC hardware article.

Return EXACT format:

TITLE: ...
DESCRIPTION: ...
TAGS: ...

Rules:
- TITLE max 60 characters
- DESCRIPTION max 155 characters
- TAGS must be comma-separated from:
CPU, GPU, RAM, SSD, NVMe, PCIe, Motherboard, VRM, Cooling, Benchmarking, Architecture

ARTICLE:
{article}
"""

    return call_model("llama3.2:1b", prompt)

def parse_seo(text):
    title = ""
    desc = ""
    tags = ""

    for line in text.split("\n"):
        line = line.strip()

        if line.startswith("TITLE:"):
            title = line.replace("TITLE:", "").strip()

        elif line.startswith("DESCRIPTION:"):
            desc = line.replace("DESCRIPTION:", "").strip()

        elif line.startswith("TAGS:"):
            tags = line.replace("TAGS:", "").strip()

    return title, desc, tags

# ----------------------------
# Improve
# ----------------------------
def improve_article(article):
    prompt = f"""
Improve this PC hardware article:

- Fix grammar
- Improve clarity
- Remove repetition
- Make it more professional
- Keep technical accuracy

ARTICLE:
{article}
"""

    return call_model("mistral:7b", prompt)
# ----------------------------
# UTILITIES
# ----------------------------
def extract_title(article):
    match = re.search(r"# (.+)", article)
    return match.group(1).strip() if match else "Untitled Hardware Post"

def slugify(title):
    return re.sub(r'[^a-z0-9]+', '-', title.lower()).strip('-')

def markdown_to_html(md):
    return markdown.markdown(md or "")

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
        title,
        slug,
        html,
        seo_title,
        seo_desc,
        tags,
        "draft",   # 👈 IMPORTANT: admin approval system
        datetime.now()
    ))

    conn.commit()
    cursor.close()
    conn.close()

# ----------------------------
# MAIN PIPELINE
# ----------------------------
if __name__ == "__main__":

    print("Generating topic... (llama3.2:1b)")
    topic = generate_topic()
    print("TOPIC:", topic)

    if topic_exists(topic):
        print("Duplicate topic, skipping...")
        exit()

    save_topic(topic)

    print("Generating article... (llama3.2:3b)")
    article = generate_article(topic)

    print("Improving article... (mistral:7b)")
    article = improve_article(article)

    print("Generating SEO... (llama3.2:1b)")
    seo_raw = generate_seo(article)
    seo_title, seo_desc, tags = parse_seo(seo_raw)

    title = extract_title(article)
    slug = slugify(title)
    html = markdown_to_html(article)

    print("Saving post...")
    save_post(title, slug, html, seo_title, seo_desc, tags)

    print("DONE")