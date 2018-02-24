-- Get Votes
SELECT TR_literalTranslationVote.translation_id, TR_literalTranslationVote.vote
FROM TR_literalTranslationVote
WHERE TR_literalTranslationVote.translator_id = $translator_id;