import networkx as nx

G = nx.read_edgelist("extract_links/data/edgeList.txt", create_using=nx.DiGraph())

pr = nx.pagerank(G,  alpha=0.85, personalization=None, max_iter=30, tol=1e-06, nstart=None, weight='weight', dangling=None)

fw = open('external_pageRankFile','w')

for p in pr:
    fw.write("/Volumes/Lu's Seagate HD/BigData/solr-7.1.0/BG/" + str(p) + "=" + str(pr[p]) + "\n")

fw.close()
